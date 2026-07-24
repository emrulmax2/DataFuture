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
    <div id="siteSettingsPage" class="ss-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Communication Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Common SMTP</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="server"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Configure the outgoing mail servers used to deliver system email, and choose which account is the default sender.</p>
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
                    @php($settingsSidebarIcon = 'settings-2')
                    @php($settingsSidebarSubtitle = 'Global configuration')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-card ss-smtp-card">
                        <div class="ss-table-card__header">
                            <h2>Common SMTP List</h2>
                            <button data-tw-toggle="modal" data-tw-target="#addSmtpModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add New SMTP
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
                            <div id="smtpSettingsListTable" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="addSmtpModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <form method="POST" action="#" id="addSmtpForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-smtp-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add SMTP</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="smtp_user">SMTP Email <span>*</span></label>
                                    <input id="smtp_user" type="text" name="smtp_user" class="ss-modal-input form-control smtp_user" placeholder="sender@example.com">
                                    <div class="acc__input-error error-smtp_user"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="smtp_pass">App Password <span>*</span></label>
                                    <input id="smtp_pass" type="password" name="smtp_pass" class="ss-modal-input form-control smtp_pass" placeholder="App password">
                                    <div class="acc__input-error error-smtp_pass"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="smtp_email_password">Email Password</label>
                                    <input id="smtp_email_password" type="password" name="smtp_email_password" class="ss-modal-input form-control smtp_email_password" placeholder="Mailbox password">
                                    <div class="acc__input-error error-smtp_email_password"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="smtp_host">SMTP Host <span>*</span></label>
                                    <input id="smtp_host" type="text" name="smtp_host" value="smtp.gmail.com" class="ss-modal-input form-control smtp_host" placeholder="smtp.gmail.com">
                                    <div class="acc__input-error error-smtp_host"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="smtp_port">SMTP Port <span>*</span></label>
                                    <input id="smtp_port" type="text" name="smtp_port" value="587" class="ss-modal-input form-control smtp_port" placeholder="587">
                                    <div class="acc__input-error error-smtp_port"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="smtp_encryption">SMTP Encryption <span>*</span></label>
                                    <div class="ss-modal-select">
                                        <select id="smtp_encryption" name="smtp_encryption" class="smtp_encryption">
                                            <option selected value="tls">TLS</option>
                                            <option value="ssl">SSL</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </div>
                                    <div class="acc__input-error error-smtp_encryption"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="smtp_authentication">SMTP Authentication <span>*</span></label>
                                    <div class="ss-modal-select">
                                        <select id="smtp_authentication" name="smtp_authentication" class="smtp_authentication">
                                            <option selected value="true">TRUE</option>
                                            <option value="false">FALSE</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </div>
                                    <div class="acc__input-error error-smtp_authentication"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--third">
                                    <label for="is_default">Default Account</label>
                                    <label class="ss-status-toggle ss-status-toggle--inline" for="is_default">
                                        <input id="is_default" class="is_default" name="is_default" type="checkbox" value="1" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Is Default?</strong>
                                            <small>Not default</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveSMTP" class="ss-btn ss-btn--primary">
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

        <div id="editSmtpModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <form method="POST" action="#" id="editSmtpForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-smtp-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit SMTP</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="edit_smtp_user">SMTP Email <span>*</span></label>
                                    <input id="edit_smtp_user" type="text" name="smtp_user" class="ss-modal-input form-control smtp_user" placeholder="sender@example.com">
                                    <div class="acc__input-error error-smtp_user"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="edit_smtp_pass">App Password <span>*</span></label>
                                    <input id="edit_smtp_pass" type="password" name="smtp_pass" class="ss-modal-input form-control smtp_pass" placeholder="App password">
                                    <div class="acc__input-error error-smtp_pass"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="edit_smtp_email_password">Email Password</label>
                                    <input id="edit_smtp_email_password" type="password" name="smtp_email_password" class="ss-modal-input form-control smtp_email_password" placeholder="Mailbox password">
                                    <div class="acc__input-error error-smtp_email_password"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="edit_smtp_host">SMTP Host <span>*</span></label>
                                    <input id="edit_smtp_host" type="text" name="smtp_host" class="ss-modal-input form-control smtp_host" placeholder="smtp.gmail.com">
                                    <div class="acc__input-error error-smtp_host"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="edit_smtp_port">SMTP Port <span>*</span></label>
                                    <input id="edit_smtp_port" type="text" name="smtp_port" class="ss-modal-input form-control smtp_port" placeholder="587">
                                    <div class="acc__input-error error-smtp_port"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="edit_smtp_encryption">SMTP Encryption <span>*</span></label>
                                    <div class="ss-modal-select">
                                        <select id="edit_smtp_encryption" name="smtp_encryption" class="smtp_encryption">
                                            <option value="">Please Select</option>
                                            <option value="tls">TLS</option>
                                            <option value="ssl">SSL</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </div>
                                    <div class="acc__input-error error-smtp_encryption"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="edit_smtp_authentication">SMTP Authentication <span>*</span></label>
                                    <div class="ss-modal-select">
                                        <select id="edit_smtp_authentication" name="smtp_authentication" class="smtp_authentication">
                                            <option value="">Please Select</option>
                                            <option value="true">TRUE</option>
                                            <option value="false">FALSE</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </div>
                                    <div class="acc__input-error error-smtp_authentication"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--third">
                                    <label for="edit_is_default">Default Account</label>
                                    <label class="ss-status-toggle ss-status-toggle--inline" for="edit_is_default">
                                        <input id="edit_is_default" class="is_default" name="is_default" type="checkbox" value="1" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Is Default?</strong>
                                            <small>Not default</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="updateSMTP" class="ss-btn ss-btn--primary">
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
                            <button type="button" data-tw-dismiss="modal" class="successDismissBtn ss-btn ss-btn--primary">Ok</button>
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
    @vite('resources/js/common-smtp.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
