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
        $bankImagePlaceholder = asset('build/assets/images/placeholders/200x200.jpg');
    @endphp

    <div id="siteSettingsPage" class="ss-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Accounts Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Banks</span>
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
                        <p>Maintain the bank accounts money is received into and paid out of.</p>
                    </div>
                </div>
                <a href="{{ route('site.setting') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Settings
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php($settingsSidebarIcon = 'settings-2')
                    @php($settingsSidebarSubtitle = 'Global configuration')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-card ss-banks-card">
                        <div class="ss-table-card__header">
                            <h2>Banks</h2>
                            <button data-tw-toggle="modal" data-tw-target="#addBankModal" type="button" class="add_btn ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add New Bank
                            </button>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query">
                                        <i data-lucide="search"></i>
                                        <input id="query" name="query" type="text" placeholder="Search...">
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Status</span>
                                    <label class="ss-filter-select" for="status">
                                        <select id="status" name="status">
                                            <option value="1">Active</option>
                                            <option value="2">Archived</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <button id="tabulator-html-filter-go" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                            </form>

                            <div class="ss-table-actions">
                                <button id="tabulator-print" type="button" class="ss-btn ss-btn--light ss-btn--tool">
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
                                                <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text"></i>
                                                    Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
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
                            <div id="bankListTable" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        {{-- The image picker reuses the shared modal upload layout introduced on Process List. --}}
        <div id="addBankModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <form method="POST" action="#" id="addBankForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-banks-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add Bank</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-process-form-grid">
                                <div class="ss-modal-field ss-process-image-field">
                                    <label>Bank Logo</label>
                                    <label for="bankPhotoAdd" class="ss-process-image-picker">
                                        <img alt="Bank logo" class="bankImageAdd" id="bankImageAdd" data-placeholder="{{ $bankImagePlaceholder }}" src="{{ $bankImagePlaceholder }}">
                                        <span><i data-lucide="camera"></i></span>
                                    </label>
                                    <small data-ss-upload-name>No file selected</small>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="photo" class="ss-process-image-input" id="bankPhotoAdd">
                                </div>

                                <div class="ss-process-fields">
                                    <div class="ss-modal-field">
                                        <label for="bank_name">Bank Name <span>*</span></label>
                                        <input id="bank_name" type="text" name="bank_name" class="ss-modal-input bank_name" placeholder="Bank name">
                                        <div class="acc__input-error error-bank_name"></div>
                                    </div>

                                    <div class="ss-modal-grid">
                                        <div class="ss-modal-field">
                                            <label for="opening_balance">Opening Balance</label>
                                            <input id="opening_balance" step="any" type="number" name="opening_balance" class="ss-modal-input opening_balance" placeholder="0.00">
                                            <div class="acc__input-error error-opening_balance"></div>
                                        </div>
                                        <div class="ss-modal-field">
                                            <label for="opening_date">Opening Date</label>
                                            <input id="opening_date" type="text" name="opening_date" class="ss-modal-input datepicker opening_date" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                                            <div class="acc__input-error error-opening_date"></div>
                                        </div>
                                    </div>

                                    <div class="ss-modal-field">
                                        <label for="ac_name">Account Name</label>
                                        <input id="ac_name" type="text" name="ac_name" class="ss-modal-input ac_name" placeholder="Account name">
                                        <div class="acc__input-error error-ac_name"></div>
                                    </div>

                                    <div class="ss-modal-grid">
                                        <div class="ss-modal-field">
                                            <label for="sort_code">Sort Code</label>
                                            <input id="sort_code" type="text" name="sort_code" class="ss-modal-input theSortcode sort_code" placeholder="00-00-00">
                                            <div class="acc__input-error error-sort_code"></div>
                                        </div>
                                        <div class="ss-modal-field">
                                            <label for="ac_number">Account Number</label>
                                            <input id="ac_number" type="number" name="ac_number" class="ss-modal-input theAcNumber ac_number" placeholder="00000000">
                                            <div class="acc__input-error error-ac_number"></div>
                                        </div>
                                    </div>

                                    <div class="ss-modal-grid">
                                        <div class="ss-modal-field">
                                            <label for="audit_status">Audit Status</label>
                                            <label class="ss-status-toggle" for="audit_status">
                                                <input id="audit_status" name="audit_status" value="1" type="checkbox" autocomplete="off">
                                                <span class="ss-status-toggle__control">
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                </span>
                                                <span class="ss-status-toggle__copy">
                                                    <strong>Not audited</strong>
                                                    <small>Excluded from audit reporting</small>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="ss-modal-field">
                                            <label for="status_add">Active Status</label>
                                            <label class="ss-status-toggle" for="status_add">
                                                <input checked id="status_add" name="status" value="1" type="checkbox" autocomplete="off">
                                                <span class="ss-status-toggle__control">
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                </span>
                                                <span class="ss-status-toggle__copy">
                                                    <strong>Active</strong>
                                                    <small>Selectable when recording transactions</small>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveBank" class="ss-btn ss-btn--primary">
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

        <div id="editBankModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <form method="POST" action="#" id="editBankForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-banks-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Bank</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-process-form-grid">
                                <div class="ss-modal-field ss-process-image-field">
                                    <label>Bank Logo</label>
                                    <label for="bankPhotoEdit" class="ss-process-image-picker">
                                        <img alt="Bank logo" class="bankImageEdit" id="bankImageEdit" data-placeholder="{{ $bankImagePlaceholder }}" src="{{ $bankImagePlaceholder }}">
                                        <span><i data-lucide="camera"></i></span>
                                    </label>
                                    <small data-ss-upload-name>No file selected</small>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="photo" class="ss-process-image-input" id="bankPhotoEdit">
                                </div>

                                <div class="ss-process-fields">
                                    <div class="ss-modal-field">
                                        <label for="edit_bank_name">Bank Name <span>*</span></label>
                                        <input id="edit_bank_name" type="text" name="bank_name" class="ss-modal-input bank_name" placeholder="Bank name">
                                        <div class="acc__input-error error-bank_name"></div>
                                    </div>

                                    <div class="ss-modal-grid">
                                        <div class="ss-modal-field">
                                            <label for="edit_opening_balance">Opening Balance</label>
                                            <input id="edit_opening_balance" step="any" type="number" name="opening_balance" class="ss-modal-input opening_balance" placeholder="0.00">
                                            <div class="acc__input-error error-opening_balance"></div>
                                        </div>
                                        <div class="ss-modal-field">
                                            <label for="edit_opening_date">Opening Date</label>
                                            <input id="edit_opening_date" type="text" name="opening_date" class="ss-modal-input datepicker opening_date" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                                            <div class="acc__input-error error-opening_date"></div>
                                        </div>
                                    </div>

                                    <div class="ss-modal-field">
                                        <label for="edit_ac_name">Account Name</label>
                                        <input id="edit_ac_name" type="text" name="ac_name" class="ss-modal-input ac_name" placeholder="Account name">
                                        <div class="acc__input-error error-ac_name"></div>
                                    </div>

                                    <div class="ss-modal-grid">
                                        <div class="ss-modal-field">
                                            <label for="edit_sort_code">Sort Code</label>
                                            <input id="edit_sort_code" type="text" name="sort_code" class="ss-modal-input theSortcode sort_code" placeholder="00-00-00">
                                            <div class="acc__input-error error-sort_code"></div>
                                        </div>
                                        <div class="ss-modal-field">
                                            <label for="edit_ac_number">Account Number</label>
                                            <input id="edit_ac_number" type="number" name="ac_number" class="ss-modal-input theAcNumber ac_number" placeholder="00000000">
                                            <div class="acc__input-error error-ac_number"></div>
                                        </div>
                                    </div>

                                    <div class="ss-modal-grid">
                                        <div class="ss-modal-field">
                                            <label for="edit_audit_status">Audit Status</label>
                                            <label class="ss-status-toggle" for="edit_audit_status">
                                                <input id="edit_audit_status" name="audit_status" value="1" type="checkbox" autocomplete="off">
                                                <span class="ss-status-toggle__control">
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                </span>
                                                <span class="ss-status-toggle__copy">
                                                    <strong>Not audited</strong>
                                                    <small>Excluded from audit reporting</small>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="ss-modal-field">
                                            <label for="edit_status">Active Status</label>
                                            <label class="ss-status-toggle" for="edit_status">
                                                <input id="edit_status" name="status" value="1" type="checkbox" autocomplete="off">
                                                <span class="ss-status-toggle__control">
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                </span>
                                                <span class="ss-status-toggle__copy">
                                                    <strong>Inactive</strong>
                                                    <small>Not selectable when recording transactions</small>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="updateBank" class="ss-btn ss-btn--primary">
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
                            <input type="hidden" name="id" value="0" />
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
    @vite('resources/js/acc-banks.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
