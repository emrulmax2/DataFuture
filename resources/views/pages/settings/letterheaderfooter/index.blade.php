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
        $letterAudienceOptions = [
            ['id' => 'letter_for_1', 'footer_id' => 'letter_for_4', 'value' => 'for_letter', 'label' => 'Letter', 'note' => 'Generated letters'],
            ['id' => 'letter_for_2', 'footer_id' => 'letter_for_5', 'value' => 'for_email', 'label' => 'Email', 'note' => 'Email templates'],
            ['id' => 'letter_for_3', 'footer_id' => 'letter_for_6', 'value' => 'for_staff', 'label' => 'Staff', 'note' => 'Staff documents'],
        ];
    @endphp

    <div id="siteSettingsPage" class="ss-page ss-letter-header-footer-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Communication Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Letter Header & Footer</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="panel-top"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Manage letterhead and footer artwork used across letters, emails and staff communications.</p>
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
                    @php($settingsSidebarIcon = 'panel-top')
                    @php($settingsSidebarSubtitle = 'Communication settings')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-stack">
                        <div class="ss-table-card ss-letter-assets-card ss-letter-header-card">
                            <div class="ss-table-card__header">
                                <h2>Letter Headers</h2>
                                <button data-tw-toggle="modal" data-tw-target="#uploadLetterHeaderModal" type="button" class="upload_headbtn ss-btn ss-btn--primary ss-btn--compact">
                                    <i data-lucide="plus"></i>
                                    Upload Header
                                </button>
                            </div>

                            <div class="ss-table-tools">
                                <form id="tabulatorFilterForm-HEADER" class="ss-table-filter">
                                    <div class="ss-filter-field">
                                        <span>Query</span>
                                        <label class="ss-filter-input" for="query-HEADER">
                                            <i data-lucide="search"></i>
                                            <input id="query-HEADER" name="query-HEADER" type="text" placeholder="Search...">
                                        </label>
                                    </div>
                                    <div class="ss-filter-field">
                                        <span>Status</span>
                                        <label class="ss-filter-select" for="status-HEADER">
                                            <select id="status-HEADER" name="status-HEADER">
                                                <option value="1">Active</option>
                                                <option value="2">Archived</option>
                                            </select>
                                            <i data-lucide="chevron-down"></i>
                                        </label>
                                    </div>
                                    <button id="tabulator-html-filter-go-HEADER" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                    <button id="tabulator-html-filter-reset-HEADER" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                                </form>

                                <div class="ss-table-actions">
                                    <button id="tabulator-print-HEADER" type="button" class="ss-btn ss-btn--light ss-btn--tool">
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
                                                    <a id="tabulator-export-csv-HEADER" href="javascript:;" class="dropdown-item">
                                                        <i data-lucide="file-text"></i>
                                                        Export CSV
                                                    </a>
                                                </li>
                                                <li>
                                                    <a id="tabulator-export-xlsx-HEADER" href="javascript:;" class="dropdown-item">
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
                                <div id="letterHeaderListTable" class="ss-tabulator table-report table-report--tabulator"></div>
                            </div>
                        </div>

                        <div class="ss-table-card ss-letter-assets-card ss-letter-footer-card">
                            <div class="ss-table-card__header">
                                <h2>Letter Footers</h2>
                                <button data-tw-toggle="modal" data-tw-target="#uploadLetterFooterModal" type="button" class="upload_footbtn ss-btn ss-btn--primary ss-btn--compact">
                                    <i data-lucide="plus"></i>
                                    Upload Footer
                                </button>
                            </div>

                            <div class="ss-table-tools">
                                <form id="tabulatorFilterForm-FOOTER" class="ss-table-filter">
                                    <div class="ss-filter-field">
                                        <span>Query</span>
                                        <label class="ss-filter-input" for="query-FOOTER">
                                            <i data-lucide="search"></i>
                                            <input id="query-FOOTER" name="query-FOOTER" type="text" placeholder="Search...">
                                        </label>
                                    </div>
                                    <div class="ss-filter-field">
                                        <span>Status</span>
                                        <label class="ss-filter-select" for="status-FOOTER">
                                            <select id="status-FOOTER" name="status-FOOTER">
                                                <option value="1">Active</option>
                                                <option value="2">Archived</option>
                                            </select>
                                            <i data-lucide="chevron-down"></i>
                                        </label>
                                    </div>
                                    <button id="tabulator-html-filter-go-FOOTER" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                    <button id="tabulator-html-filter-reset-FOOTER" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                                </form>

                                <div class="ss-table-actions">
                                    <button id="tabulator-print-FOOTER" type="button" class="ss-btn ss-btn--light ss-btn--tool">
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
                                                    <a id="tabulator-export-csv-FOOTER" href="javascript:;" class="dropdown-item">
                                                        <i data-lucide="file-text"></i>
                                                        Export CSV
                                                    </a>
                                                </li>
                                                <li>
                                                    <a id="tabulator-export-xlsx-FOOTER" href="javascript:;" class="dropdown-item">
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
                                <div id="letterFooterListTable" class="ss-tabulator table-report table-report--tabulator"></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="uploadLetterHeaderModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-letter-upload-modal">
                    <div class="ss-settings-modal__header">
                        <div>
                            <span></span>
                            <h2>Upload Header</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <div class="modal-body ss-settings-modal__body">
                        <div class="ss-modal-grid">
                            <div class="ss-modal-field ss-modal-field--full">
                                <label for="display_name">Name <span>*</span></label>
                                <input id="display_name" type="text" name="display_name" class="ss-modal-input form-control" placeholder="Header name">
                                <div class="headername-error error-name acc__input-error"></div>
                            </div>

                            <div class="ss-modal-field ss-modal-field--full ss-document-choices ss-letter-audience-field">
                                <div class="ss-document-choices__heading">
                                    <span>Header For <em>*</em></span>
                                    <small>Choose where this header is available.</small>
                                </div>
                                <div class="ss-document-toggle-grid ss-letter-audience-grid">
                                    @foreach($letterAudienceOptions as $option)
                                        <label class="ss-status-toggle ss-letter-audience-toggle" for="{{ $option['id'] }}">
                                            <input id="{{ $option['id'] }}" class="letter_for_options" type="checkbox" value="{{ $option['value'] }}" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>{{ $option['label'] }}</strong>
                                                <small>{{ $option['note'] }}</small>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="acc__input-error error-for"></div>
                            </div>

                            <div class="ss-modal-field ss-modal-field--full">
                                <label>Header Image <span>*</span></label>
                                <form method="post" action="{{ route('letterheaderfooter.upload.letterhead') }}" class="dropzone ss-dropzone ss-letter-upload-dropzone" id="uploadLetterHeadForm" enctype="multipart/form-data">
                                    @csrf
                                    <div class="fallback">
                                        <input name="documents[]" accept="image/*" multiple type="file">
                                    </div>
                                    <div class="dz-message" data-dz-message>
                                        <i data-lucide="upload-cloud"></i>
                                        <strong>Drop image here or click to upload</strong>
                                        <small>Max file size 20MB and one file per upload.</small>
                                    </div>
                                    <input type="hidden" name="type" value="Header">
                                    <input type="hidden" name="name" value="">
                                    <input type="hidden" name="for_letter" value="No">
                                    <input type="hidden" name="for_email" value="No">
                                    <input type="hidden" name="for_staff" value="No">
                                </form>
                                <div class="acc__input-error error-file"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer ss-settings-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                        <button type="button" id="uploadHeaderBtn" class="ss-btn ss-btn--primary">
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
            </div>
        </div>

        <div id="uploadLetterFooterModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-letter-upload-modal">
                    <div class="ss-settings-modal__header">
                        <div>
                            <span></span>
                            <h2>Upload Footer</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <div class="modal-body ss-settings-modal__body">
                        <div class="ss-modal-grid">
                            <div class="ss-modal-field ss-modal-field--full">
                                <label for="footer_display_name">Name <span>*</span></label>
                                <input id="footer_display_name" type="text" name="footer_display_name" class="ss-modal-input form-control" placeholder="Footer name">
                                <div class="acc__input-error error-name"></div>
                            </div>

                            <div class="ss-modal-field ss-modal-field--full ss-document-choices ss-letter-audience-field">
                                <div class="ss-document-choices__heading">
                                    <span>Footer For <em>*</em></span>
                                    <small>Choose where this footer is available.</small>
                                </div>
                                <div class="ss-document-toggle-grid ss-letter-audience-grid">
                                    @foreach($letterAudienceOptions as $option)
                                        <label class="ss-status-toggle ss-letter-audience-toggle" for="{{ $option['footer_id'] }}">
                                            <input id="{{ $option['footer_id'] }}" class="letter_for_options" type="checkbox" value="{{ $option['value'] }}" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>{{ $option['label'] }}</strong>
                                                <small>{{ $option['note'] }}</small>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="acc__input-error error-footer_dispaly_for"></div>
                            </div>

                            <div class="ss-modal-field ss-modal-field--full">
                                <label>Footer Image <span>*</span></label>
                                <form method="post" action="{{ route('letterheaderfooter.upload.letterfoot') }}" class="dropzone ss-dropzone ss-letter-upload-dropzone" id="uploadLetterFootForm" enctype="multipart/form-data">
                                    @csrf
                                    <div class="fallback">
                                        <input name="documents[]" accept="image/*" multiple type="file">
                                    </div>
                                    <div class="dz-message" data-dz-message>
                                        <i data-lucide="upload-cloud"></i>
                                        <strong>Drop image here or click to upload</strong>
                                        <small>Max file size 20MB and one file per upload.</small>
                                    </div>
                                    <input type="hidden" name="type" value="Footer">
                                    <input type="hidden" name="name" value="">
                                    <input type="hidden" name="for_letter" value="No">
                                    <input type="hidden" name="for_email" value="No">
                                    <input type="hidden" name="for_staff" value="No">
                                </form>
                                <div class="acc__input-error error-file"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer ss-settings-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                        <button type="button" id="uploadFooterBtn" class="ss-btn ss-btn--primary">
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
                            <button type="button" data-tw-dismiss="modal" data-action="DISMISS" class="successCloser ss-btn ss-btn--primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content ss-success-modal ss-warning-modal">
                    <div class="modal-body p-0">
                        <div class="ss-success-modal__body">
                            <i data-lucide="alert-octagon" class="ss-success-modal__icon"></i>
                            <div class="warningModalTitle"></div>
                            <p class="warningModalDesc"></p>
                        </div>
                        <div class="ss-success-modal__footer">
                            <button type="button" data-tw-dismiss="modal" data-action="DISMISS" class="warningCloser ss-btn ss-btn--primary">Ok</button>
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
                        <button type="button" class="disAgreeWith ss-btn ss-btn--light">
                            <i data-lucide="x"></i>
                            No, Cancel
                        </button>
                        <button type="button" data-recordid="0" data-status="none" class="agreeWith ss-btn ss-btn--danger">
                            <i data-lucide="check"></i>
                            Yes, I agree
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="letterheadConfirmModal" class="modal" tabindex="-1" aria-hidden="true">
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
                        <button type="button" class="disAgreeWith ss-btn ss-btn--light">
                            <i data-lucide="x"></i>
                            No, Cancel
                        </button>
                        <button type="button" data-recordid="0" data-status="none" class="agreeWith ss-btn ss-btn--danger">
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
    @vite('resources/js/letterfooter.js')
    @vite('resources/js/letterheader.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
