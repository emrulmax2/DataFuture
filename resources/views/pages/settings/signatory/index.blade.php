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
    <div id="siteSettingsPage" class="ss-page ss-signatory-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Communication Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Signatory</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="file-signature"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Manage staff signatories and signature images used in generated letters and communication templates.</p>
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
                    @php($settingsSidebarIcon = 'file-signature')
                    @php($settingsSidebarSubtitle = 'Communication settings')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-card ss-signatory-card">
                        <div class="ss-table-card__header">
                            <h2>Signatory List</h2>
                            <button data-tw-toggle="modal" data-tw-target="#addSignatoryModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add Signatory
                            </button>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm-SG" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query-SG">
                                        <i data-lucide="search"></i>
                                        <input id="query-SG" name="query" type="text" placeholder="Search...">
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Status</span>
                                    <label class="ss-filter-select" for="status-SG">
                                        <select id="status-SG" name="status">
                                            <option value="1">Active</option>
                                            <option value="2">Archived</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <button id="tabulator-html-filter-go-SG" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                <button id="tabulator-html-filter-reset-SG" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                            </form>

                            <div class="ss-table-actions">
                                <button id="tabulator-print-SG" type="button" class="ss-btn ss-btn--light ss-btn--tool">
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
                                                <a id="tabulator-export-csv-SG" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text"></i>
                                                    Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx-SG" href="javascript:;" class="dropdown-item">
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
                            <div id="signatoryListTable" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="addSignatoryModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <form method="POST" action="#" id="addSignatoryForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-signatory-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add Signatory</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field">
                                    <label for="signatory_name">Signatory Name <span>*</span></label>
                                    <input id="signatory_name" type="text" name="signatory_name" class="ss-modal-input form-control signatory_name" placeholder="Full name">
                                    <div class="acc__input-error error-signatory_name"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="signatory_post">Signatory Post <span>*</span></label>
                                    <input id="signatory_post" type="text" name="signatory_post" class="ss-modal-input form-control signatory_post" placeholder="Designation">
                                    <div class="acc__input-error error-signatory_post"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full ss-signatory-upload-field">
                                    <label>Signature Image <span>*</span></label>
                                    <label for="addSignatoryDocument" class="ss-signature-upload ss-signatory-upload signatory">
                                        <input type="file" accept=".jpeg,.jpg,.png,.gif" name="signatory" id="addSignatoryDocument">
                                        <span class="ss-signature-upload__icon">
                                            <i data-lucide="upload-cloud"></i>
                                        </span>
                                        <span class="ss-signature-upload__copy">
                                            <strong>Upload Signature Image</strong>
                                            <small id="addSignatoryDocumentName" class="signatoryDocumentName">No file selected</small>
                                        </span>
                                    </label>
                                    <div class="acc__input-error error-signatory"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveSignatorySet" class="ss-btn ss-btn--primary">
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
                </form>
            </div>
        </div>

        <div id="editSignatoryModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <form method="POST" action="#" id="editSignatoryForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-signatory-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Signatory</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field">
                                    <label for="edit_signatory_name">Signatory Name <span>*</span></label>
                                    <input id="edit_signatory_name" type="text" name="signatory_name" class="ss-modal-input form-control signatory_name" placeholder="Full name">
                                    <div class="acc__input-error error-signatory_name"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="edit_signatory_post">Signatory Post <span>*</span></label>
                                    <input id="edit_signatory_post" type="text" name="signatory_post" class="ss-modal-input form-control signatory_post" placeholder="Designation">
                                    <div class="acc__input-error error-signatory_post"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full ss-signatory-upload-field">
                                    <div class="ss-signatory-upload-heading">
                                        <label>Signature Image</label>
                                        <a href="#" target="_blank" download class="downloadExistAttachment ss-existing-signature-link" style="display: none;">
                                            <i data-lucide="download"></i>
                                            Existing signature
                                        </a>
                                    </div>
                                    <label for="editSignatoryDocument" class="ss-signature-upload ss-signatory-upload signatory">
                                        <input type="file" accept=".jpeg,.jpg,.png,.gif" name="signatory" id="editSignatoryDocument">
                                        <span class="ss-signature-upload__icon">
                                            <i data-lucide="upload-cloud"></i>
                                        </span>
                                        <span class="ss-signature-upload__copy">
                                            <strong>Upload Replacement Image</strong>
                                            <small id="editSignatoryDocumentName" class="signatoryDocumentName">No file selected</small>
                                        </span>
                                    </label>
                                    <div class="acc__input-error error-signatory"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="updateSignatorySet" class="ss-btn ss-btn--primary">
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
                            <input type="hidden" name="id" value="0">
                        </div>
                    </div>
                </form>
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
    @vite('resources/js/signatory.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
