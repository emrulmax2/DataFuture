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
    <div id="siteSettingsPage" class="ss-page ss-communication-template-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Communication Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>System Communication Templates</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="messages-square"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Manage the system email and SMS templates used by automated communication workflows.</p>
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
                    @php($settingsSidebarIcon = 'messages-square')
                    @php($settingsSidebarSubtitle = 'Communication settings')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-card ss-communication-template-card">
                        <div class="ss-table-card__header">
                            <h2>System Communication Templates</h2>
                            <button data-tw-toggle="modal" data-tw-target="#addTemplateModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add Template
                            </button>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm-LS" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query-LS">
                                        <i data-lucide="search"></i>
                                        <input id="query-LS" name="query" type="text" placeholder="Search...">
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Status</span>
                                    <label class="ss-filter-select" for="status-LS">
                                        <select id="status-LS" name="status">
                                            <option value="1">Active</option>
                                            <option value="2">Archived</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <button id="tabulator-html-filter-go-LS" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                <button id="tabulator-html-filter-reset-LS" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                            </form>

                            <div class="ss-table-actions">
                                <button id="tabulator-print-LS" type="button" class="ss-btn ss-btn--light ss-btn--tool">
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
                                                <a id="tabulator-export-csv-LS" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text"></i>
                                                    Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx-LS" href="javascript:;" class="dropdown-item">
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
                            <div id="CommunTemplateListTable" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="addTemplateModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--editor">
                <form method="POST" action="#" id="addTemplateForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-communication-template-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add Communication Template</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label>Type <span>*</span></label>
                                    <div class="ss-type-options ss-template-type-options">
                                        <label class="ss-type-option ss-template-type-option" for="type_1">
                                            <input checked id="type_1" class="templateType" type="radio" name="type" value="1" autocomplete="off">
                                            <span><i data-lucide="mail"></i></span>
                                            <div class="ss-template-type-copy">
                                                <strong>Email</strong>
                                                <small>Rich text content</small>
                                            </div>
                                        </label>
                                        <label class="ss-type-option ss-template-type-option" for="type_2">
                                            <input id="type_2" class="templateType" type="radio" name="type" value="2" autocomplete="off">
                                            <span><i data-lucide="smartphone"></i></span>
                                            <div class="ss-template-type-copy">
                                                <strong>SMS</strong>
                                                <small>Text message body</small>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="acc__input-error error-type"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="name">Name <span>*</span></label>
                                    <input id="name" type="text" name="name" class="ss-modal-input form-control name" placeholder="Template name">
                                    <div class="acc__input-error error-name"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full ss-editor-field emailContentWrap">
                                    <label for="addEditor">Email Content <span>*</span></label>
                                    <div class="editor document-editor ss-editor email_content">
                                        <div class="document-editor__toolbar"></div>
                                        <div class="document-editor__editable-container">
                                            <div class="document-editor__editable" id="addEditor"></div>
                                        </div>
                                    </div>
                                    <div class="acc__input-error error-email_content"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full smsContentWrap" style="display: none;">
                                    <div class="ss-sms-description-label">
                                        <label for="sms_content">SMS Content <span>*</span></label>
                                        <span class="sms_countr ss-sms-counter">160 / 1</span>
                                    </div>
                                    <textarea name="sms_content" id="sms_content" rows="8" class="ss-modal-input ss-modal-textarea form-control sms_content" placeholder="SMS message body"></textarea>
                                    <div class="acc__input-error error-sms_content"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveTemplate" class="ss-btn ss-btn--primary">
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

        <div id="editTemplateModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--editor">
                <form method="POST" action="#" id="editTemplateForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-communication-template-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Communication Template</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label>Type <span>*</span></label>
                                    <div class="ss-type-options ss-template-type-options">
                                        <label class="ss-type-option ss-template-type-option" for="edit_type_1">
                                            <input checked id="edit_type_1" class="templateType" type="radio" name="type" value="1" autocomplete="off">
                                            <span><i data-lucide="mail"></i></span>
                                            <div class="ss-template-type-copy">
                                                <strong>Email</strong>
                                                <small>Rich text content</small>
                                            </div>
                                        </label>
                                        <label class="ss-type-option ss-template-type-option" for="edit_type_2">
                                            <input id="edit_type_2" class="templateType" type="radio" name="type" value="2" autocomplete="off">
                                            <span><i data-lucide="smartphone"></i></span>
                                            <div class="ss-template-type-copy">
                                                <strong>SMS</strong>
                                                <small>Text message body</small>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="acc__input-error error-type"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="edit_name">Name <span>*</span></label>
                                    <input id="edit_name" type="text" name="name" class="ss-modal-input form-control name" placeholder="Template name">
                                    <div class="acc__input-error error-name"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full ss-editor-field emailContentWrap">
                                    <label for="editEditor">Email Content <span>*</span></label>
                                    <div class="editor document-editor ss-editor email_content">
                                        <div class="document-editor__toolbar"></div>
                                        <div class="document-editor__editable-container">
                                            <div class="document-editor__editable" id="editEditor"></div>
                                        </div>
                                    </div>
                                    <div class="acc__input-error error-email_content"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full smsContentWrap" style="display: none;">
                                    <div class="ss-sms-description-label">
                                        <label for="edit_sms_content">SMS Content <span>*</span></label>
                                        <span class="sms_countr ss-sms-counter">160 / 1</span>
                                    </div>
                                    <textarea name="sms_content" id="edit_sms_content" rows="8" class="ss-modal-input ss-modal-textarea form-control sms_content" placeholder="SMS message body"></textarea>
                                    <div class="acc__input-error error-sms_content"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="editTemplates" class="ss-btn ss-btn--primary">
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
    @vite('resources/js/communication-template.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
