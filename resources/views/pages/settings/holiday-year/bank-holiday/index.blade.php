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
        $yearStart = (isset($theYear->start_date) && !empty($theYear->start_date)) ? date('Y', strtotime($theYear->start_date)) : '';
        $yearEnd = (isset($theYear->end_date) && !empty($theYear->end_date)) ? date('Y', strtotime($theYear->end_date)) : '';
        $holidayYearLabel = trim($yearStart.' - '.$yearEnd, ' -');
    @endphp

    <div id="siteSettingsPage" class="ss-page ss-holiday-year-page ss-bank-holiday-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <a href="{{ route('holiday.year') }}">Holiday Years</a>
            <i data-lucide="chevron-right"></i>
            <span>Bank Holidays</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="landmark"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Review, import and maintain bank holidays for {{ $holidayYearLabel ?: 'the selected holiday year' }}.</p>
                    </div>
                </div>
                <a href="{{ route('holiday.year') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Holiday Years
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php($settingsSidebarIcon = 'calendar')
                    @php($settingsSidebarSubtitle = 'HR holiday setup')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-card ss-holiday-year-card ss-bank-holiday-card">
                        <div class="ss-table-card__header">
                            <div>
                                <h2>Bank Holidays</h2>
                                <p>
                                    Holiday Year
                                    <span class="ss-bank-holiday-year-badge">{{ $holidayYearLabel ?: 'Not set' }}</span>
                                </p>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#bankHolidayImportModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="upload"></i>
                                Import Bank Holidays
                            </button>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm-BHY" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query-BHY">
                                        <i data-lucide="search"></i>
                                        <input id="query-BHY" name="query" type="text" placeholder="Search holidays...">
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Status</span>
                                    <label class="ss-filter-select" for="status-BHY">
                                        <select id="status-BHY" name="status">
                                            <option value="1">Active</option>
                                            <option value="2">Archived</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <button id="tabulator-html-filter-go-BHY" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                <button id="tabulator-html-filter-reset-BHY" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                            </form>

                            <div class="ss-table-actions">
                                <button id="tabulator-print-BHY" type="button" class="ss-btn ss-btn--light ss-btn--tool">
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
                                                <a id="tabulator-export-csv-BHY" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text"></i>
                                                    Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx-BHY" href="javascript:;" class="dropdown-item">
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
                            <div id="hrBankHolidayList" data-year="{{ $theYear->id ?? 0 }}" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="editBankHolidayModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-bank-holiday-modal-dialog">
                <form method="POST" action="#" id="editBankHolidayForm" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-bank-holiday-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Bank Holiday</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid ss-bank-holiday-form-grid">
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="name">Title <span>*</span></label>
                                    <input id="name" type="text" name="name" class="ss-modal-input name" placeholder="Bank holiday title">
                                    <div class="acc__input-error error-name"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="start_date">Start Date <span>*</span></label>
                                    <input id="start_date" type="text" name="start_date" class="ss-modal-input start_date" placeholder="DD-MM-YYYY">
                                    <div class="acc__input-error error-start_date"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="end_date">End Date <span>*</span></label>
                                    <input id="end_date" type="text" name="end_date" class="ss-modal-input end_date" placeholder="DD-MM-YYYY">
                                    <div class="acc__input-error error-end_date"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="duration">Duration <span>*</span></label>
                                    <input id="duration" type="number" min="1" name="duration" class="ss-modal-input duration" placeholder="1">
                                    <div class="acc__input-error error-duration"></div>
                                </div>
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" class="ss-modal-input ss-modal-textarea description" rows="4" placeholder="Optional notes for this holiday"></textarea>
                                    <div class="acc__input-error error-description"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="updateBH" class="ss-btn ss-btn--primary">
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

        <div id="bankHolidayImportModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--import">
                <div class="modal-content ss-settings-modal">
                    <div class="ss-settings-modal__header">
                        <div>
                            <span></span>
                            <h2>Import Bank Holidays</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <div class="modal-body ss-settings-modal__body">
                        <form method="post" action="{{ route('hr.bank.holiday.import') }}" class="dropzone ss-dropzone" id="bankHolidayImportForm" enctype="multipart/form-data">
                            @csrf
                            <div class="fallback">
                                <input name="file" type="file">
                            </div>
                            <div class="dz-message" data-dz-message>
                                <i data-lucide="upload-cloud"></i>
                                <strong>Drop file here or click to upload.</strong>
                                <small>Use the sample file format for clean imports.</small>
                            </div>
                            <input type="hidden" name="hr_holiday_year_id" value="{{ $theYear->id ?? 0 }}">
                        </form>
                    </div>
                    <div class="modal-footer ss-settings-modal__footer ss-settings-modal__footer--split">
                        <a href="{{ route('hr.bank.holiday.export', $theYear->id ?? 0) }}" id="downloadSample" class="ss-btn ss-btn--light">
                            <i data-lucide="download"></i>
                            Download Sample
                        </a>
                        <div class="ss-modal-footer-actions">
                            <button type="button" data-tw-dismiss="modal" class="closeImportModal ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button id="saveImportHoliday" type="button" class="ss-btn ss-btn--primary">
                                <i data-lucide="upload"></i>
                                Upload
                            </button>
                        </div>
                    </div>
                </div>
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
    @vite('resources/js/bank-holiday.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
