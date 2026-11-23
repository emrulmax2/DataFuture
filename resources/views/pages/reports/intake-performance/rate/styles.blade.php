{{--
    Styles for the new Continuation Rate and Retention Rate reports (Reports ›
    Intake Performance, "New version" tabs): the version tabs, the form, the
    result cards and table. Included once by intake-performance/index.blade.php.
    Prefixed `crn-` and scoped to the new reports' markup.
--}}
<style>
    /* --- The page's report list: one card per report (partials/report-head.blade.php) ---
       Scoped to the accordion's id so it outranks the theme's .accordion rules. */
    /* --- Page header (index.blade.php) --- */
    .ipr-pagehead { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px 24px; margin-top: 8px; font-family: Inter, system-ui, -apple-system, "Segoe UI", Arial, Helvetica, sans-serif; }
    .ipr-pagehead__lead { flex: 1 1 420px; display: flex; align-items: center; gap: 16px; min-width: 0; }
    .ipr-pagehead__icon {
        flex: 0 0 52px; display: inline-flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 14px;
        background: linear-gradient(135deg, #0e5a54 0%, #1b8a7f 100%); color: #ffffff; box-shadow: 0 6px 16px rgba(14, 90, 84, 0.22);
    }
    .ipr-pagehead__icon svg { width: 26px; height: 26px; stroke-width: 1.75; }
    .ipr-pagehead__text { min-width: 0; }
    .ipr-pagehead__title { margin: 0; color: #16242b; font-size: 20px; font-weight: 700; line-height: 1.25; }
    .ipr-pagehead__sub { margin: 3px 0 0; color: #55616b; font-size: 13px; line-height: 1.45; }
    .ipr-back {
        display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border: 1px solid #c3c9d1; border-radius: 8px;
        background: #ffffff; color: #16242b !important; font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap;
        transition: border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }
    .ipr-back svg { width: 16px; height: 16px; transition: transform 0.2s ease; }
    .ipr-back:hover { border-color: #0e5a54; color: #0e5a54 !important; box-shadow: 0 2px 8px rgba(22, 36, 43, 0.08); }
    .ipr-back:hover svg { transform: translateX(-3px); }
    .ipr-back:focus-visible { outline: 2px solid #1668a8; outline-offset: 2px; }
    /* Below md the theme's mobile menu bar is fixed over the top of the content. */
    @media (max-width: 767px) { .ipr-pagehead { margin-top: 40px; } }
    /* Icons on the old reports' Generate buttons sit after their loader in the markup; this shows them first. */
    .ipr-btn-lead { order: -1; }

    #intakePerformanceReportAccordion.ipr-list { display: flex; flex-direction: column; gap: 12px; font-family: Inter, system-ui, -apple-system, "Segoe UI", Arial, Helvetica, sans-serif; }
    .ipr-tone-teal   { --ipr-accent: #0e5a54; --ipr-tint: #e2f1ee; --ipr-line: #9fcfc6; }
    .ipr-tone-blue   { --ipr-accent: #1668a8; --ipr-tint: #e3eef8; --ipr-line: #a9c9e6; }
    .ipr-tone-amber  { --ipr-accent: #9a5b00; --ipr-tint: #fbf0d9; --ipr-line: #e8c98a; }
    .ipr-tone-violet { --ipr-accent: #5b47b0; --ipr-tint: #ece9fa; --ipr-line: #c2b9ec; }
    .ipr-tone-rust   { --ipr-accent: #b4521a; --ipr-tint: #fbeadb; --ipr-line: #eec0a0; }
    .ipr-tone-rose   { --ipr-accent: #a8325e; --ipr-tint: #fbe7ef; --ipr-line: #eab4c8; }
    #intakePerformanceReportAccordion.ipr-list > .accordion-item {
        position: relative; margin: 0; padding: 0; border: 1px solid #dfe3e7; border-radius: 12px; background: #ffffff;
        box-shadow: 0 1px 2px rgba(22, 36, 43, 0.04); transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    #intakePerformanceReportAccordion.ipr-list > .accordion-item::before {
        content: ""; position: absolute; left: -1px; top: 14px; bottom: 14px; width: 3px; border-radius: 0 3px 3px 0;
        background: var(--ipr-accent); opacity: 0; transition: opacity 0.2s ease, top 0.2s ease, bottom 0.2s ease;
    }
    #intakePerformanceReportAccordion.ipr-list > .accordion-item:hover { border-color: #c3c9d1; box-shadow: 0 4px 14px rgba(22, 36, 43, 0.06); }
    #intakePerformanceReportAccordion.ipr-list > .accordion-item:hover::before { opacity: 1; }
    #intakePerformanceReportAccordion.ipr-list > .accordion-item:has(> .accordion-collapse.show) {
        border-color: var(--ipr-line); box-shadow: 0 0 0 3px var(--ipr-tint), 0 8px 24px rgba(22, 36, 43, 0.06);
    }
    #intakePerformanceReportAccordion.ipr-list > .accordion-item:has(> .accordion-collapse.show)::before { opacity: 1; top: 18px; bottom: auto; height: 40px; }

    #intakePerformanceReportAccordion.ipr-list .accordion-header .ipr-head {
        display: flex; align-items: center; gap: 16px; width: 100%; margin: 0; padding: 16px 20px; border: 0; border-radius: 12px;
        background: transparent; color: #16242b; font: inherit; text-align: left; cursor: pointer;
    }
    #intakePerformanceReportAccordion.ipr-list .accordion-header .ipr-head:focus { outline: none; }
    #intakePerformanceReportAccordion.ipr-list .accordion-header .ipr-head:focus-visible { outline: 2px solid #1668a8; outline-offset: -2px; }
    .ipr-icon {
        flex: 0 0 44px; display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px;
        border-radius: 11px; background: var(--ipr-tint); color: var(--ipr-accent); transition: transform 0.2s ease;
    }
    .ipr-icon svg { width: 22px; height: 22px; stroke-width: 1.75; }
    .ipr-head:hover .ipr-icon { transform: rotate(-6deg) scale(1.05); }
    .ipr-text { flex: 1 1 auto; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .ipr-title { color: #16242b; font-size: 16px; font-weight: 600; line-height: 1.3; }
    .ipr-desc { color: #55616b; font-size: 13px; font-weight: 400; line-height: 1.4; }
    .ipr-formats { display: inline-flex; gap: 6px; flex-shrink: 0; }
    .ipr-formats span { padding: 2px 8px; border: 1px solid #dfe3e7; border-radius: 999px; color: #55616b; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; line-height: 1.5; }
    .ipr-chevron {
        flex: 0 0 34px; display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px;
        border-radius: 50%; background: #f3f5f7; color: #55616b; transition: transform 0.25s ease, background-color 0.2s ease, color 0.2s ease;
    }
    .ipr-chevron svg { width: 18px; height: 18px; }
    .ipr-head:hover .ipr-chevron { background: var(--ipr-tint); color: var(--ipr-accent); }
    .ipr-head:not(.collapsed) .ipr-chevron { transform: rotate(180deg); background: var(--ipr-accent); color: #ffffff; }
    .ipr-head:not(.collapsed) .ipr-title { color: var(--ipr-accent); }

    #intakePerformanceReportAccordion.ipr-list .accordion-collapse { margin: 0; }
    #intakePerformanceReportAccordion.ipr-list .accordion-body { margin: 0 20px; padding: 20px 0 24px; border-top: 1px solid #eef1f3; }

    @media (max-width: 640px) {
        #intakePerformanceReportAccordion.ipr-list .accordion-header .ipr-head { gap: 12px; padding: 14px 14px; }
        .ipr-icon { flex-basis: 38px; width: 38px; height: 38px; border-radius: 10px; }
        .ipr-icon svg { width: 19px; height: 19px; }
        .ipr-formats { display: none; }
        #intakePerformanceReportAccordion.ipr-list .accordion-body { margin: 0 14px; }
    }
    @media (prefers-reduced-motion: reduce) {
        #intakePerformanceReportAccordion.ipr-list > .accordion-item, #intakePerformanceReportAccordion.ipr-list > .accordion-item::before,
        .ipr-icon, .ipr-chevron { transition: none; }
        .ipr-head:hover .ipr-icon { transform: none; }
    }

    .crn-tabs { display: inline-flex; gap: 4px; padding: 4px; margin-bottom: 18px; background: #eef1f3; border-radius: 8px; }
    .crn-tab { border: 0; background: transparent; color: #55616b; font-size: 13px; font-weight: 600; padding: 7px 14px; border-radius: 6px; cursor: pointer; }
    .crn-tab.is-active { background: #ffffff; color: #0e5a54; box-shadow: 0 1px 2px rgba(22, 36, 43, 0.12); }
    .crn-tab:focus-visible { outline: 2px solid #1668a8; outline-offset: 2px; }

    .crn { color: #16242b; font-family: Inter, system-ui, -apple-system, "Segoe UI", Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.45; }
    .crn-form { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 16px; }
    /* At least 300px; grows with its chips up to 640px, then they wrap. */
    .crn-field { display: flex; flex-direction: column; gap: 6px; flex: 0 1 auto; min-width: 300px; max-width: min(100%, 640px); }
    .crn-label { font-size: 13px; font-weight: 500; }
    .crn-error { color: #b42318; font-size: 12px; min-height: 0; }
    .crn-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .crn-btn { display: inline-flex; align-items: center; gap: 8px; height: 44px; padding: 0 16px; border: 0; border-radius: 6px; color: #ffffff !important; font: inherit; font-weight: 600; cursor: pointer; text-decoration: none; }
    .crn-btn--primary { background: #0e5a54; }
    .crn-btn--primary:hover { background: #0b4a45; }
    .crn-btn--blue { background: #1668a8; }
    .crn-btn--blue:hover { background: #125a91; }
    .crn-btn--soft { background: #d6ebf8; color: #0f4c7a !important; }
    .crn-btn--soft:hover { background: #c4e1f4; }
    .crn-btn[disabled] { opacity: 0.7; cursor: wait; }
    /* --- Intake semester pickers: Tom Select 1.7.8 (.ts-control > .ts-input / .ts-dropdown), dropdown_input plugin ---
       Every picker in the report cards: the new forms (single) and the old versions (multi with remove buttons, the
       theme's .tom-selects). Scoped to the accordion's id so it outranks the theme; its chevron and focus border
       on .dropdown-active are !important, hence the !important below. */
    #intakePerformanceReportAccordion .ts-control .ts-input {
        align-items: center; min-height: 44px; padding: 0 42px 0 14px; border: 1px solid #c3c9d1; border-radius: 8px; background: #ffffff;
        color: #16242b; font-size: 14px; line-height: 1.4; cursor: pointer; transition: border-color 0.15s ease, box-shadow 0.15s ease; box-shadow: none;
    }
    #intakePerformanceReportAccordion .ts-control .ts-input:hover { border-color: #9aa4ad; }
    #intakePerformanceReportAccordion .ts-control .ts-input.focus, #intakePerformanceReportAccordion .ts-control .ts-input.dropdown-active { border: 1px solid #0e5a54; box-shadow: 0 0 0 3px rgba(14, 90, 84, 0.14); }
    #intakePerformanceReportAccordion .ts-control .ts-input, #intakePerformanceReportAccordion .ts-control .ts-input.dropdown-active { background-image: none !important; }
    #intakePerformanceReportAccordion .ts-control .ts-input::before { display: none; }
    #intakePerformanceReportAccordion .ts-control .ts-input::after {
        content: ""; position: absolute; top: 50%; right: 14px; width: 16px; height: 16px; margin-top: -8px;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2355616b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") center / 16px no-repeat;
        transition: transform 0.2s ease;
    }
    #intakePerformanceReportAccordion .ts-control .ts-input.dropdown-active::after { transform: rotate(180deg); }
    #intakePerformanceReportAccordion .ts-control .ts-input > .item { color: #16242b; font-size: 14px; font-weight: 500; line-height: 1.4; }
    #intakePerformanceReportAccordion .ts-control .ts-input > .item[data-value=""] { color: #8a949c; font-weight: 400; }
    #intakePerformanceReportAccordion .ts-dropdown {
        z-index: 60; margin: 6px 0 0; padding: 6px; border: 1px solid #dfe3e7; border-radius: 10px; background: #ffffff; color: #16242b; font-size: 14px;
        box-shadow: 0 14px 32px rgba(22, 36, 43, 0.14), 0 2px 6px rgba(22, 36, 43, 0.06);
    }
    #intakePerformanceReportAccordion .ts-dropdown .dropdown-input-wrap { padding: 0 0 6px; margin-bottom: 4px; border-bottom: 1px solid #eef1f3; }
    #intakePerformanceReportAccordion .ts-dropdown .dropdown-input {
        height: 38px; padding: 0 12px 0 36px; border: 1px solid #dfe3e7; border-radius: 8px; color: #16242b; font-size: 14px; box-shadow: none;
        background: #f6f8f9 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238a949c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cline x1='20' y1='20' x2='16.65' y2='16.65'/%3E%3C/svg%3E") 12px center / 16px no-repeat;
    }
    #intakePerformanceReportAccordion .ts-dropdown .dropdown-input::placeholder { color: #8a949c; }
    #intakePerformanceReportAccordion .ts-dropdown .dropdown-input:focus { outline: none; border-color: #0e5a54; background-color: #ffffff; box-shadow: 0 0 0 3px rgba(14, 90, 84, 0.12); }
    #intakePerformanceReportAccordion .ts-dropdown .ts-dropdown-content { max-height: 264px; padding-right: 2px; scrollbar-width: thin; scrollbar-color: #c9cfd5 transparent; }
    #intakePerformanceReportAccordion .ts-dropdown .ts-dropdown-content::-webkit-scrollbar { width: 8px; }
    #intakePerformanceReportAccordion .ts-dropdown .ts-dropdown-content::-webkit-scrollbar-thumb { border: 2px solid #ffffff; border-radius: 8px; background: #c9cfd5; }
    #intakePerformanceReportAccordion .ts-dropdown .option { position: relative; padding: 8px 34px 8px 10px; border-radius: 6px; color: #16242b; font-size: 14px; line-height: 1.35; cursor: pointer; }
    #intakePerformanceReportAccordion .ts-dropdown .option:hover, #intakePerformanceReportAccordion .ts-dropdown .option.active { background: #e2f1ee; color: #0e5a54; }
    #intakePerformanceReportAccordion .ts-dropdown .option.selected { font-weight: 600; }
    #intakePerformanceReportAccordion .ts-dropdown .option.selected::after {
        content: ""; position: absolute; top: 50%; right: 10px; width: 16px; height: 16px; margin-top: -8px;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230e5a54' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E") center / 16px no-repeat;
    }
    /* "Please select" is the empty placeholder option: shown in the box, not offered in the list. */
    #intakePerformanceReportAccordion .ts-dropdown .option[data-value=""] { display: none; }
    #intakePerformanceReportAccordion .ts-dropdown .highlight { border-radius: 2px; background: rgba(224, 160, 48, 0.3); color: inherit; }
    #intakePerformanceReportAccordion .ts-dropdown .no-results { padding: 10px; color: #55616b; font-size: 13px; }
    /* Multi (every picker but Application Analysis): the designs' chips, and "Please select" while empty (the search box sits in the dropdown). */
    #intakePerformanceReportAccordion .ts-control.multi .ts-input { gap: 6px; padding: 6px 42px 6px 10px; }
    #intakePerformanceReportAccordion .ts-control.multi .ts-input:not(.has-items) { padding-left: 14px; }
    #intakePerformanceReportAccordion .ts-control.multi .ts-input:not(.has-items)::before {
        content: "Please select"; display: block; position: static; height: auto; background: none; color: #8a949c; font-size: 14px;
    }
    #intakePerformanceReportAccordion .ts-control.multi .ts-input > .item {
        display: inline-flex; align-items: center; gap: 2px; height: 30px; margin: 0; padding: 0 0 0 10px !important; border: 0; border-radius: 4px;
        background: #dff0ec; color: #0b4a45; font-size: 13px; font-weight: 600; line-height: 1;
    }
    #intakePerformanceReportAccordion .ts-control.multi .ts-input > .item.active { background: #0e5a54; color: #ffffff; }
    #intakePerformanceReportAccordion .ts-control.plugin-remove_button .ts-input > .item .remove {
        display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; margin: 0; padding: 0;
        border: 0; border-radius: 4px; color: inherit; font-size: 15px; font-weight: 700; line-height: 1; text-decoration: none; transition: background-color 0.15s ease;
    }
    #intakePerformanceReportAccordion .ts-control.plugin-remove_button .ts-input > .item .remove:hover { background: rgba(11, 74, 69, 0.12); }
    #intakePerformanceReportAccordion .ts-control.plugin-remove_button .ts-input > .item.active .remove:hover { background: rgba(255, 255, 255, 0.22); }

    .crn-btn__icon { width: 16px; height: 16px; flex-shrink: 0; stroke-width: 2; }
    /* While a report loads, the spinner takes the icon's place. */
    .crn-btn .crn-spinner { order: -1; }
    .crn-btn[disabled] .crn-btn__icon { display: none; }
    .crn-btn:focus-visible { outline: 2px solid #1668a8; outline-offset: 2px; }

    .crn-result { display: flex; flex-direction: column; gap: 22px; margin-top: 22px; }
    .crn-notice { padding: 14px 18px; border-radius: 6px; background: #fbeadb; color: #8a3d0c; }
    .crn-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
    .crn-kpi { padding: 16px 18px; border: 1px solid #dfe3e7; border-radius: 6px; }
    .crn-kpi--main { background: #e2f1ee; border-color: #e2f1ee; }
    .crn-kpi__label { color: #55616b; font-size: 13px; font-weight: 600; }
    .crn-kpi--main .crn-kpi__label, .crn-kpi--main .crn-kpi__value { color: #0e5a54; }
    .crn-kpi__value { font-size: 34px; font-weight: 700; line-height: 1.15; }
    .crn-kpi__value--down { color: #8a3d0c; }
    .crn-kpi__note { color: #55616b; font-size: 13px; }
    .crn-kpi--main .crn-kpi__note { color: #16242b; }

    .crn-tablewrap { overflow-x: auto; }
    .crn-table { width: 100%; min-width: 820px; border-collapse: collapse; font-size: 14px; }
    .crn-table th { padding: 10px 0; border-bottom: 1px solid #16242b; color: #55616b; font-size: 13px; font-weight: 600; text-align: right; white-space: nowrap; }
    .crn-table td { padding: 12px 0; border-bottom: 1px solid #e3e7ea; text-align: right; vertical-align: middle; }
    .crn-table .crn-table__course { text-align: left; }
    .crn-table .crn-table__bar { width: 28%; padding-left: 16px; text-align: left; }
    .crn-table .crn-table__rate { width: 72px; font-weight: 700; }
    .crn-table .crn-table__rate.is-low { color: #8a3d0c; }
    .crn-table .crn-table__rate--none { color: #55616b; font-size: 13px; font-weight: 400; }
    .crn-table .crn-table__num { width: 100px; }
    .crn-table .crn-table__num--wide { width: 110px; }
    .crn-table .crn-table__num--wider { width: 130px; }
    .crn-table tr.is-before-total td, .crn-table tr.is-last td { border-bottom-color: #16242b; }
    .crn-table__total td { border-bottom: 0; font-weight: 700; }
    .crn-course { font-weight: 600; }
    .crn-course__sub { color: #55616b; font-size: 12px; }
    .crn-track { height: 12px; background: #e9edf0; border-radius: 2px; }
    .crn-track__fill { height: 100%; background: #0e5a54; border-radius: 2px; }
    .crn-track__fill.is-low { background: #c8601a; }
    .crn-track__fill.is-total { background: #16242b; }
    .crn-footnote { color: #55616b; font-size: 13px; }

    @media (max-width: 640px) {
        .crn-field { width: 100%; min-width: 0; }
        .crn-actions, .crn-btn { width: 100%; }
        .crn-btn { justify-content: center; }
        .ipr-pagehead__icon { flex-basis: 44px; width: 44px; height: 44px; border-radius: 12px; }
        .ipr-pagehead__icon svg { width: 22px; height: 22px; }
        .ipr-pagehead__title { font-size: 18px; }
        .crn-kpi__value { font-size: 28px; }
        .crn-sub-cards { grid-template-columns: minmax(0, 1fr); }
    }

    /* --- Attendance Rate (new version) --- */
    .crn-att-section { display: flex; flex-direction: column; gap: 6px; }
    .crn-att-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 8px; }
    .crn-att-title { margin: 0; font-size: 15px; font-weight: 700; }
    .crn-att-meta { color: #55616b; font-size: 13px; }
    .crn-att-table { min-width: 900px; }
    .crn-att-table td { padding: 14px 0; }
    .crn-att-table .crn-att-course { font-weight: 700; }
    .crn-att-table .crn-att-bar { width: 230px; padding-left: 16px; text-align: left; }
    .crn-att-table .crn-att-track { height: 14px; }
    .crn-att-table .crn-att-rate { width: 70px; font-size: 17px; font-weight: 700; }
    .crn-att-table .crn-att-rate.is-low { color: #8a3d0c; }
    .crn-att-table .crn-att-num { width: 90px; }
    .crn-att-table .crn-att-term { width: 100px; }
    .crn-att-table .crn-att-term.is-wide { width: 130px; }
    .crn-att-table .crn-att-term.is-none { color: #55616b; }
    .crn-att-table .crn-table__total .crn-att-term { font-weight: 400; }
    .crn-att-details { border-top: 1px solid #dfe3e7; padding-top: 14px; }
    .crn-att-details summary { font-size: 15px; font-weight: 700; padding: 12px 0; cursor: pointer; }
    .crn-att-details summary:focus-visible { outline: 2px solid #1668a8; outline-offset: 2px; }
    .crn-att-detail { width: 100%; min-width: 940px; border-collapse: collapse; font-size: 13px; line-height: 1.3; margin-top: 8px; }
    .crn-att-detail th { padding: 6px 0 6px 10px; border-bottom: 1px solid #16242b; color: #55616b; font-size: 12px; font-weight: 600; text-align: right; white-space: nowrap; }
    .crn-att-detail td { padding: 7px 0 7px 10px; border-bottom: 1px solid #e3e7ea; text-align: right; }
    .crn-att-detail .d-name { padding-left: 8px; text-align: left; }
    .crn-att-detail .d-rate { padding-right: 8px; }
    .crn-att-detail tr.d-course td, .crn-att-detail tr.d-all td { background: #e6ecef; font-weight: 700; }
    .crn-att-detail tr.d-course td { border-bottom-color: #c3c9d1; }
    .crn-att-detail tr.d-term .d-name { padding-left: 24px; }
    .crn-att-detail tr.d-even td { background: #f4f6f8; }
    .crn-att-detail tr.d-all td { border-bottom-color: #16242b; }
    .crn-att-early { color: #55616b; font-size: 13px; padding-top: 10px; }
    .crn-att-toggle { display: inline-flex; align-items: center; gap: 8px; padding: 0; border: 0; background: none; color: inherit; font: inherit; font-weight: 700; text-align: left; cursor: pointer; }
    .crn-att-toggle::before { content: ""; flex: 0 0 auto; width: 0; height: 0; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-left: 6px solid #55616b; transition: transform 0.15s ease; }
    .crn-att-toggle[aria-expanded="true"]::before { transform: rotate(90deg); }
    .crn-att-toggle:hover { color: #0e5a54; }
    .crn-att-toggle:focus-visible { outline: 2px solid #1668a8; outline-offset: 2px; }

    /* --- Submission & Pass Rate (new version) --- */
    .crn-sub-top { display: flex; flex-wrap: wrap; gap: 12px; align-items: stretch; }
    /* Two by two beside the waffle panel (four auto-fit cards wrap 3 + 1 at desktop width). */
    .crn-sub-cards { flex: 1 1 420px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .crn-sub-panel { flex: 1 1 460px; box-sizing: border-box; padding: 16px 18px; border: 1px solid #dfe3e7; border-radius: 6px; display: flex; flex-direction: column; gap: 12px; }
    .crn-sub-panel__body { display: flex; flex-wrap: wrap; gap: 24px; align-items: center; }
    .crn-sub-waffle { display: grid; grid-template-columns: repeat(10, 14px); gap: 4px; flex-shrink: 0; }
    .crn-sub-waffle span { width: 14px; height: 14px; border-radius: 2px; }
    .crn-sub-legend { display: flex; flex-direction: column; gap: 12px; }
    .crn-sub-legend__item { display: flex; gap: 10px; align-items: flex-start; }
    .crn-sub-legend__swatch { width: 12px; height: 12px; margin-top: 7px; border-radius: 2px; flex-shrink: 0; }
    .crn-sub-legend__big { font-size: 22px; font-weight: 700; line-height: 1.2; }
    .crn-sub-legend__big span { font-size: 14px; font-weight: 600; }
    .crn-sub-legend__sub { color: #55616b; font-size: 12px; }
    .crn-sub .is-passed, .crn-sub-waffle .is-passed, .crn-sub-legend .is-passed, .crn-sub-stack .is-passed { background: #0e5a54; }
    .crn-sub-waffle .is-notpassed, .crn-sub-legend .is-notpassed, .crn-sub-stack .is-notpassed { background: #e0a030; }
    .crn-sub-waffle .is-missing, .crn-sub-legend .is-missing, .crn-sub-stack .is-missing { background: #c8601a; }
    .crn-sub-table { min-width: 860px; }
    .crn-sub-table td { padding: 14px 0; }
    .crn-sub-table .crn-sub-barcell { width: 36%; padding-left: 16px; text-align: left; }
    .crn-sub-stack { display: flex; gap: 2px; height: 16px; border-radius: 2px; overflow: hidden; }
    .crn-sub-stack.is-empty { background: #e9edf0; }
    .crn-sub-table .crn-sub-num { width: 90px; }
    .crn-sub-table .crn-sub-num--wide { width: 100px; }
    .crn-sub-table .crn-sub-pass { font-size: 17px; font-weight: 700; }
    .crn-sub-table .crn-sub-none { color: #55616b; }
    .crn-sub-table .crn-table__total td:not(.crn-sub-pass) { font-weight: 700; }
    .crn-sub-detail .d-rate { padding-right: 8px; }

    /* --- Award Rate (new version): one dot per student --- */
    .crn-awd-section { display: flex; flex-direction: column; }
    .crn-awd-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 8px; padding-bottom: 8px; border-bottom: 1px solid #16242b; }
    .crn-awd-legend { display: flex; flex-wrap: wrap; gap: 6px 18px; align-items: center; color: #55616b; font-size: 12px; }
    .crn-awd-key { display: inline-flex; align-items: center; gap: 6px; }
    .crn-awd-swatch { width: 10px; height: 10px; border-radius: 50%; }
    .crn-awd-swatch.is-done, .crn-awd-dots .is-done { background: #0e5a54; }
    .crn-awd-swatch.is-not, .crn-awd-dots .is-not { background: #c8601a; }
    .crn-awd-row { display: flex; flex-wrap: wrap; gap: 16px 40px; align-items: center; padding: 18px 8px; border-bottom: 1px solid #e3e7ea; }
    .crn-awd-info { flex: 0 1 330px; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .crn-awd-name { font-size: 14px; font-weight: 700; }
    .crn-awd-flag { color: #55616b; font-size: 12px; }
    .crn-awd-rate { font-size: 30px; font-weight: 700; line-height: 1.15; color: #16242b; }
    .crn-awd-rate.is-low { color: #8a3d0c; }
    .crn-awd-rate.is-none { color: #55616b; }
    .crn-awd-counts { font-size: 13px; }
    .crn-awd-counts b { font-weight: 700; }
    .crn-awd-dotwrap { flex: 1 1 420px; max-width: 760px; min-width: 0; }
    .crn-awd-dots { display: flex; flex-wrap: wrap; gap: 6px; }
    .crn-awd-dots span { width: 16px; height: 16px; border-radius: 50%; }
    @media (max-width: 640px) {
        .crn-awd-dots { gap: 4px; }
        .crn-awd-dots span { width: 12px; height: 12px; }
    }
    /* --- Grouped by intake (every new report): intake rows open and close what sits under them --- */
    .is-grp-hidden { display: none !important; }
    .crn-grp-toggle { display: inline-flex; align-items: center; gap: 8px; min-height: 28px; padding: 0; border: 0; background: transparent; color: inherit; font: inherit; font-weight: 700; text-align: left; cursor: pointer; }
    .crn-grp-toggle svg { flex: 0 0 12px; transition: transform 0.15s ease; }
    .crn-grp-toggle[aria-expanded="false"] svg { transform: rotate(-90deg); }
    .crn-grp-toggle:hover { color: #0e5a54; }
    .crn-grp-toggle:focus-visible { outline: 2px solid #1668a8; outline-offset: 2px; }
    .crn-flag { color: #55616b; font-size: 12px; font-weight: 400; }
    .crn-gtable th:first-child, .crn-gtable td:first-child { padding-left: 8px; }
    .crn-gtable th:last-child, .crn-gtable td:last-child { padding-right: 8px; }
    .crn-gtable tr.crn-grp td { background: #e6ecef; border-bottom-color: #c3c9d1; font-weight: 700; }
    .crn-gtable tr.crn-in td:first-child { padding-left: 20px; font-weight: 600; }
    .crn-gtable tr.crn-all td { border-top: 1px solid #16242b; border-bottom: 1px solid #16242b; font-weight: 700; }
    .crn-gtable .crn-g-rate { width: 66px; font-size: 15px; font-weight: 700; }
    .crn-gtable .crn-g-rate.is-low { color: #8a3d0c; }
    .crn-gtable .crn-g-rate.is-none { color: #55616b; font-size: 13px; font-weight: 400; }
    .crn-gtable .crn-g-none { color: #55616b; }
    /* Detail tables grouped by intake: intake (darker), course (shaded), terms (striped). */
    .crn-att-detail tr.d-intake td { background: #d3dce2; border-bottom-color: #16242b; font-weight: 700; }
    .crn-att-detail.is-grouped tr.d-course .d-name { padding-left: 22px; }
    .crn-att-detail.is-grouped tr.d-term .d-name { padding-left: 36px; }
    /* Award: an intake heading over its courses' dots. */
    .crn-awd-grp { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 4px 12px; padding: 8px; background: #e6ecef; border-bottom: 1px solid #c3c9d1; font-weight: 700; }
</style>
