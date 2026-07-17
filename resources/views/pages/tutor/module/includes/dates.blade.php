<div id="planClassDatesAccordion" class="accordion tm-date-accordion">
    <div class="accordion-item tm-panel">
        <div id="planClassDatesAccordion-1" class="accordion-header">
            <button class="accordion-button tm-accordion-button relative w-full" type="button" data-tw-toggle="collapse" data-tw-target="#planClassDatesAccordion-collapse-1" aria-expanded="true" aria-controls="planClassDatesAccordion-collapse-1">
                <span class="tm-accordion-title">
                    <span class="tm-accordion-icon"><i data-lucide="calendar-days" class="w-5 h-5"></i></span>
                    <span>
                        <span>Theory Class Dates</span>
                        <small>{{ $planDateList->count() }} sessions</small>
                    </span>
                </span>
                <span class="accordionCollaps"></span>
            </button>
        </div>
        <div id="planClassDatesAccordion-collapse-1" class="accordion-collapse collapse show" aria-labelledby="planClassDatesAccordion-1" data-tw-parent="#planClassDatesAccordion">
            <div class="accordion-body p-0">
                <div class="tm-toolbar">
                    <form id="tabulatorFilterForm-PD" class="tm-filter">
                        <label>
                            Date
                            <span class="tm-date-input">
                                <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                                <input id="dates-PD" name="dates" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                            </span>
                        </label>
                        <label>
                            Status
                            <select id="status-PD" name="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="2">Archived</option>
                            </select>
                        </label>
                        <button id="tabulator-html-filter-go-PD" type="button" class="btn btn-primary">Go</button>
                        <button id="tabulator-html-filter-reset-PD" type="button" class="btn btn-secondary">Reset</button>
                    </form>
                    <div class="tm-actions">
                        <button id="tabulator-print" class="btn btn-outline-secondary">
                            <i data-lucide="printer" class="w-4 h-4"></i> Print
                        </button>
                        <div class="dropdown">
                            <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="file-text" class="w-4 h-4"></i> Export <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </button>
                            <div class="dropdown-menu w-40">
                                <ul class="dropdown-content">
                                    <li>
                                        <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tm-table-wrap">
                    <div id="classPlanDateListsTutorTable" data-planid="{{ $plan->id }}" class="table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($plan->tutorial->id) && $plan->tutorial->id > 0)
        <div class="accordion-item tm-panel">
            <div id="planClassDatesAccordion-2" class="accordion-header">
                <button class="accordion-button collapsed tm-accordion-button relative w-full" type="button" data-tw-toggle="collapse" data-tw-target="#planClassDatesAccordion-collapse-2" aria-expanded="false" aria-controls="planClassDatesAccordion-collapse-2">
                    <span class="tm-accordion-title">
                        <span class="tm-accordion-icon"><i data-lucide="calendar-days" class="w-5 h-5"></i></span>
                        <span>
                            <span>Tutorial Class Dates</span>
                            <small>{{ $tutorialPlanDateCount ?? 0 }} sessions</small>
                        </span>
                    </span>
                    <span class="accordionCollaps"></span>
                </button>
            </div>
            <div id="planClassDatesAccordion-collapse-2" class="accordion-collapse collapse" aria-labelledby="planClassDatesAccordion-2" data-tw-parent="#planClassDatesAccordion">
                <div class="accordion-body p-0">
                    <div class="tm-toolbar">
                        <form id="tabulatorFilterForm-TPD" class="tm-filter">
                            <label>
                                Date
                                <span class="tm-date-input">
                                    <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                                    <input id="dates-TPD" name="dates" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                                </span>
                            </label>
                            <label>
                                Status
                                <select id="status-TPD" name="status" class="form-select">
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </label>
                            <button id="tabulator-html-filter-go-TPD" type="button" class="btn btn-primary">Go</button>
                            <button id="tabulator-html-filter-reset-TPD" type="button" class="btn btn-secondary">Reset</button>
                        </form>
                        <div class="tm-actions">
                            <button id="tabulator-print-TPD" class="btn btn-outline-secondary">
                                <i data-lucide="printer" class="w-4 h-4"></i> Print
                            </button>
                            <div class="dropdown">
                                <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4"></i> Export <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-TPD" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-TPD" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tm-table-wrap">
                        <div id="classPlanDateListsTutorialTable" data-planid="{{ $plan->id }}" data-tutorialid="{{ $plan->tutorial->id }}" class="table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    #tutorModuleDetails .tm-date-accordion {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    #tutorModuleDetails .tm-date-accordion .tm-panel {
        border-color: #e6e1d3;
        border-radius: 18px;
        box-shadow: 0 1px 3px rgba(16, 49, 46, .05);
        padding-bottom: 0;
    }

    #tutorModuleDetails .tm-accordion-button {
        align-items: center;
        background: #f4f8f6;
        border: 0;
        border-bottom: 1px solid #e6ede9;
        border-radius: 18px 18px 0 0;
        display: flex;
        justify-content: space-between;
        min-height: 75px;
        padding: 18px 24px;
        text-align: left;
    }

    #tutorModuleDetails .accordion-button.collapsed {
        background: #fff;
        border-bottom: 0;
        border-radius: 18px;
    }

    #tutorModuleDetails .tm-accordion-button::after {
        display: none;
    }

    #tutorModuleDetails .tm-accordion-title {
        align-items: center;
        display: flex;
        gap: 12px;
    }

    #tutorModuleDetails .tm-accordion-title > span:last-child > span {
        color: var(--tm-green);
        display: block;
        font-family: "IBM Plex Serif", Georgia, serif;
        font-size: 17px;
        font-weight: 600;
    }

    #tutorModuleDetails .tm-accordion-title small {
        color: var(--tm-faint);
        display: block;
        font-size: 11.5px;
        margin-top: 1px;
    }

    #tutorModuleDetails .tm-accordion-icon {
        align-items: center;
        background: #e4f1ee;
        border-radius: 11px;
        color: var(--tm-green);
        display: flex;
        height: 38px;
        justify-content: center;
        width: 38px;
    }

    #tutorModuleDetails .accordion-button.collapsed .tm-accordion-icon {
        background: #f4f5f4;
        color: #93a09d;
    }

    #tutorModuleDetails .accordionCollaps {
        align-items: center;
        background: #fff;
        border: 1px solid #e4dcc7;
        border-radius: 8px;
        color: #0d7c73;
        display: flex;
        flex: 0 0 30px;
        height: 30px;
        justify-content: center;
        margin-left: 18px;
        position: relative !important;
        right: auto !important;
        top: auto !important;
        width: 30px;
    }

    #tutorModuleDetails .accordionCollaps::before,
    #tutorModuleDetails .accordion-button.collapsed .accordionCollaps::after {
        background: currentColor;
        border-radius: 999px;
        content: "";
        display: block;
        left: 50% !important;
        margin: 0 !important;
        opacity: 1 !important;
        position: absolute !important;
        right: auto !important;
        top: 50% !important;
        transform: translate(-50%, -50%);
        transition: none !important;
        visibility: visible !important;
    }

    #tutorModuleDetails .accordionCollaps::before {
        height: 2px !important;
        width: 14px !important;
    }

    #tutorModuleDetails .accordionCollaps::after {
        display: none !important;
        height: 14px !important;
        width: 2px !important;
    }

    #tutorModuleDetails .accordion-button.collapsed .accordionCollaps::after {
        display: block !important;
    }

    #tutorModuleDetails .tm-date-accordion .tm-toolbar {
        background: #fff;
        border-bottom: 1px solid #f0f2ec;
        gap: 16px;
        padding: 16px 24px;
    }

    #tutorModuleDetails .tm-date-accordion .tm-filter {
        gap: 12px;
    }

    #tutorModuleDetails .tm-date-accordion .tm-filter label {
        color: #8b9995;
        font-size: 12.5px;
        gap: 8px;
    }

    #tutorModuleDetails .tm-date-input {
        align-items: center;
        background: #f9f7f1;
        border: 1px solid #ded7c6;
        border-radius: 9px;
        color: #0d7c73;
        display: inline-flex;
        gap: 8px;
        min-height: 34px;
        padding: 0 12px;
        width: 126px;
    }

    #tutorModuleDetails .tm-date-input .form-control {
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        color: #adbbb9 !important;
        font-size: 12.5px !important;
        min-height: 32px;
        padding: 0 !important;
        width: 84px;
    }

    #tutorModuleDetails .tm-date-input .form-control::placeholder {
        color: #adbbb9;
        opacity: 1;
    }

    #tutorModuleDetails .tm-date-accordion .form-select {
        font-size: 13px !important;
        font-weight: 600 !important;
        min-height: 34px;
        padding: 7px 34px 7px 12px !important;
    }

    #tutorModuleDetails .tm-date-accordion .btn {
        background: #fff !important;
        border-color: #ded7c6 !important;
        color: #3f524f !important;
        font-size: 13px !important;
        min-height: 34px;
        padding: 7px 14px !important;
    }

    #tutorModuleDetails .tm-date-accordion .btn-primary {
        background: rgba(13, 124, 115, .10) !important;
        border-color: rgba(13, 124, 115, .22) !important;
        color: #0a655d !important;
        padding-left: 18px !important;
        padding-right: 18px !important;
    }

    #tutorModuleDetails .tm-date-accordion .btn-secondary {
        background: #f4f0e6 !important;
        border-color: #e4dcc7 !important;
        color: #5a6f6c !important;
        padding-left: 16px !important;
        padding-right: 16px !important;
    }

    #tutorModuleDetails .tm-date-accordion .tm-actions {
        gap: 9px;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable.tabulator,
    #tutorModuleDetails #classPlanDateListsTutorialTable.tabulator {
        border: 0 !important;
        color: #12312e;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-header,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-header {
        background: #fafaf7 !important;
        border-bottom: 2px solid #eef0ea !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-header .tabulator-col,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-header .tabulator-col {
        background: #fafaf7 !important;
        border-bottom: 0 !important;
        border-right: 0 !important;
        min-height: 42px;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-header .tabulator-col-content,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-header .tabulator-col-content {
        padding: 12px 24px !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-col-title,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-col-title {
        color: #9aa8a5;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        line-height: 1;
        text-transform: uppercase;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-col-sorter,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-col-sorter {
        display: none !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-row,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-row {
        background: #fff !important;
        border-bottom: 1px solid #f3f4f0 !important;
        min-height: 63px;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-row:nth-child(even),
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-row:nth-child(even) {
        background: #fbfbf9 !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-row .tabulator-cell,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-row .tabulator-cell {
        align-items: center;
        border-right: 0 !important;
        color: #3f524f;
        display: inline-flex;
        min-height: 63px;
        padding: 13px 24px !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-row .tabulator-cell:first-child,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-row .tabulator-cell:first-child {
        color: #93a09d;
        font-size: 12.5px;
    }

    #tutorModuleDetails .tm-date-primary {
        color: #12312e;
        font-size: 13.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    #tutorModuleDetails .tm-date-room,
    #tutorModuleDetails .tm-date-time-wrap {
        display: block;
        line-height: 1.35;
    }

    #tutorModuleDetails .tm-date-room strong {
        color: #3f524f;
        display: block;
        font-size: 13px;
        font-weight: 400;
        white-space: nowrap;
    }

    #tutorModuleDetails .tm-date-room small,
    #tutorModuleDetails .tm-date-time-wrap small {
        color: #93a09d;
        display: block;
        font-size: 11.5px;
        white-space: nowrap;
    }

    #tutorModuleDetails .tm-date-time {
        color: #5a6f6c;
        font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
        font-size: 12px;
        white-space: nowrap;
    }

    #tutorModuleDetails .tm-date-status {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-size: 11.5px;
        font-weight: 600;
        gap: 6px;
        line-height: 1;
        padding: 5px 11px;
        white-space: nowrap;
    }

    #tutorModuleDetails .tm-date-status::before {
        background: currentColor;
        border-radius: 999px;
        content: "";
        height: 6px;
        width: 6px;
    }

    #tutorModuleDetails .tm-date-status.is-completed {
        background: #e4f1ee;
        border: 1px solid #c4e2da;
        color: #0d7c73;
    }

    #tutorModuleDetails .tm-date-status.is-live {
        background: #fbeceb;
        border: 1px solid #f2cfca;
        color: #b3261e;
    }

    #tutorModuleDetails .tm-date-status.is-scheduled,
    #tutorModuleDetails .tm-date-status.is-unknown {
        background: #f1f3f1;
        border: 1px solid #e1e5e1;
        color: #8a9b98;
    }

    #tutorModuleDetails .tm-date-status.is-canceled {
        background: #fbeceb;
        border: 1px solid #f2cfca;
        color: #b3261e;
    }

    #tutorModuleDetails .tm-date-actions {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        justify-content: flex-end;
        width: 100%;
    }

    #tutorModuleDetails .tm-date-action {
        align-items: center;
        border-radius: 9px;
        cursor: pointer;
        display: inline-flex;
        font-size: 12.5px;
        font-weight: 600;
        gap: 6px;
        height: 34px;
        justify-content: center;
        line-height: 1;
        min-height: 34px;
        padding: 0 14px;
        white-space: nowrap;
    }

    #tutorModuleDetails .tm-date-action svg {
        flex: 0 0 14px;
        height: 14px;
        width: 14px;
    }

    #tutorModuleDetails .tm-date-action.is-view {
        background: rgba(13, 124, 115, .10);
        border: 1px solid rgba(13, 124, 115, .24);
        color: #0a655d;
    }

    #tutorModuleDetails .tm-date-action.is-feed {
        background: rgba(16, 150, 80, .12);
        border: 1px solid rgba(16, 150, 80, .30);
        color: #0a7c40;
    }

    #tutorModuleDetails .tm-date-action.is-end,
    #tutorModuleDetails .tm-date-action.is-canceled {
        background: rgba(179, 38, 30, .10);
        border: 1px solid rgba(179, 38, 30, .28);
        color: #b3261e;
    }

    #tutorModuleDetails .tm-date-action.is-muted {
        background: transparent;
        border: 0;
        color: #93a09d;
        cursor: default;
        padding-left: 0;
        padding-right: 0;
    }

    #tutorModuleDetails .tm-date-accordion .tabulator-footer {
        background: #fff !important;
        border-top: 1px solid #f3f4f0 !important;
        color: #8b9995 !important;
        margin: 0 !important;
        min-height: 70px;
        padding: 17px 24px !important;
        text-align: left !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-paginator,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-paginator {
        align-items: center;
        color: #8b9995 !important;
        display: flex !important;
        font-size: 12px !important;
        font-weight: 400 !important;
        gap: 5px;
        width: 100%;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-paginator > label,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-paginator > label {
        color: #8b9995 !important;
        font-size: 12px !important;
        font-weight: 400 !important;
        line-height: 30px;
        margin: 0 8px 0 0 !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-page-size,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-page-size {
        -webkit-appearance: none;
        appearance: none;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='%238a9b98' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") !important;
        background-position: center right 10px !important;
        background-repeat: no-repeat !important;
        background-size: 13px !important;
        border: 1px solid #e0e2dd !important;
        border-radius: 9px !important;
        box-shadow: none !important;
        color: #0f2d2a !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        height: 30px !important;
        line-height: 30px !important;
        margin: 0 auto 0 0 !important;
        min-height: 30px !important;
        padding: 0 28px 0 12px !important;
        width: 58px !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-pages,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-pages {
        align-items: center;
        display: inline-flex !important;
        gap: 5px;
        margin: 0 5px !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-page,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-page {
        align-items: center;
        background-color: #fff !important;
        border: 1px solid #e0e2dd !important;
        border-radius: 8px !important;
        box-shadow: none !important;
        color: #5a6f6c !important;
        display: inline-flex !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        height: 30px !important;
        justify-content: center;
        line-height: 1 !important;
        margin: 0 !important;
        min-height: 30px !important;
        min-width: 30px !important;
        padding: 0 !important;
        width: 30px !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-page.active,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-page.active {
        background-color: #0d7c73 !important;
        border-color: #0d7c73 !important;
        color: #fff !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-page:disabled,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-page:disabled {
        background-color: #fff !important;
        border-color: #e8ebe6 !important;
        color: #b6c0bd !important;
        opacity: 1 !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-page:not(.active):hover,
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-page:not(.active):hover {
        background-color: #f4f8f6 !important;
        color: #0d7c73 !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-page[data-page="first"],
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-page[data-page="first"] {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24' stroke='%238a9b98' stroke-width='1.7' fill='none' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m11 17-5-5 5-5M18 17l-5-5 5-5'/%3E%3C/svg%3E") !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        background-size: 14px !important;
        color: transparent !important;
        font-size: 0 !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-page[data-page="prev"],
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-page[data-page="prev"] {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24' stroke='%238a9b98' stroke-width='1.7' fill='none' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m15 18-6-6 6-6'/%3E%3C/svg%3E") !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        background-size: 14px !important;
        color: transparent !important;
        font-size: 0 !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-page[data-page="next"],
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-page[data-page="next"] {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24' stroke='%238a9b98' stroke-width='1.7' fill='none' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m9 18 6-6-6-6'/%3E%3C/svg%3E") !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        background-size: 14px !important;
        color: transparent !important;
        font-size: 0 !important;
    }

    #tutorModuleDetails #classPlanDateListsTutorTable .tabulator-footer .tabulator-page[data-page="last"],
    #tutorModuleDetails #classPlanDateListsTutorialTable .tabulator-footer .tabulator-page[data-page="last"] {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24' stroke='%238a9b98' stroke-width='1.7' fill='none' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m13 17 5-5-5-5M6 17l5-5-5-5'/%3E%3C/svg%3E") !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        background-size: 14px !important;
        color: transparent !important;
        font-size: 0 !important;
    }
</style>
