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
    <div id="siteSettingsPage" class="ss-page ss-doc-role-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Site Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>File Manager</span>
            <i data-lucide="chevron-right"></i>
            <span>Document Roles</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="shield-check"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Define document roles and the actions each role can perform in File Manager.</p>
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
                    @php
                        $settingsSidebarIcon = 'folder-lock';
                        $settingsSidebarSubtitle = 'File manager access';
                    @endphp
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-card ss-doc-role-card">
                        <div class="ss-table-card__header">
                            <div>
                                <h2>Roles & Permissions</h2>
                                <p>Create, read, update and delete access for document folders</p>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addPermissionModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add Permission
                            </button>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query">
                                        <i data-lucide="search"></i>
                                        <input id="query" name="query" type="text" placeholder="Search roles...">
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
                            <div id="docRolePermissionTable" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="addPermissionModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-doc-role-modal-dialog">
                <form method="POST" action="#" id="addPermissionForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-doc-role-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add Role & Permission</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field">
                                    <label for="display_name">Display Name <span>*</span></label>
                                    <input id="display_name" type="text" name="display_name" class="ss-modal-input display_name" placeholder="Display name">
                                    <div class="acc__input-error error-display_name"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="type">Type <span>*</span></label>
                                    <input id="type" type="text" name="type" class="ss-modal-input type" placeholder="Permission type">
                                    <div class="acc__input-error error-type"></div>
                                </div>
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label>Permissions <span>*</span></label>
                                    <div class="ss-doc-permission-grid" data-permission-group>
                                        <label class="ss-status-toggle ss-doc-permission-toggle" for="create">
                                            <input id="create" name="create" type="checkbox" value="1" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>Create</strong>
                                                <small>Add new documents</small>
                                            </span>
                                        </label>
                                        <label class="ss-status-toggle ss-doc-permission-toggle" for="read">
                                            <input id="read" name="read" type="checkbox" value="1" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>Read</strong>
                                                <small>View documents</small>
                                            </span>
                                        </label>
                                        <label class="ss-status-toggle ss-doc-permission-toggle" for="update">
                                            <input id="update" name="update" type="checkbox" value="1" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>Update</strong>
                                                <small>Edit documents</small>
                                            </span>
                                        </label>
                                        <label class="ss-status-toggle ss-doc-permission-toggle" for="delete">
                                            <input id="delete" name="delete" type="checkbox" value="1" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>Delete</strong>
                                                <small>Remove documents</small>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="acc__input-error error-checkboxs"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveRole" class="ss-btn ss-btn--primary">
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

        <div id="editPermissionModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-doc-role-modal-dialog">
                <form method="POST" action="#" id="editPermissionForm" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-doc-role-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Role & Permission</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field">
                                    <label for="edit_display_name">Display Name <span>*</span></label>
                                    <input id="edit_display_name" type="text" name="display_name" class="ss-modal-input display_name" placeholder="Display name">
                                    <div class="acc__input-error error-display_name"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="edit_type">Type <span>*</span></label>
                                    <input id="edit_type" type="text" name="type" class="ss-modal-input type" placeholder="Permission type">
                                    <div class="acc__input-error error-type"></div>
                                </div>
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label>Permissions <span>*</span></label>
                                    <div class="ss-doc-permission-grid" data-permission-group>
                                        <label class="ss-status-toggle ss-doc-permission-toggle" for="edit_create">
                                            <input id="edit_create" name="create" type="checkbox" value="1" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>Create</strong>
                                                <small>Add new documents</small>
                                            </span>
                                        </label>
                                        <label class="ss-status-toggle ss-doc-permission-toggle" for="edit_read">
                                            <input id="edit_read" name="read" type="checkbox" value="1" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>Read</strong>
                                                <small>View documents</small>
                                            </span>
                                        </label>
                                        <label class="ss-status-toggle ss-doc-permission-toggle" for="edit_update">
                                            <input id="edit_update" name="update" type="checkbox" value="1" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>Update</strong>
                                                <small>Edit documents</small>
                                            </span>
                                        </label>
                                        <label class="ss-status-toggle ss-doc-permission-toggle" for="edit_delete">
                                            <input id="edit_delete" name="delete" type="checkbox" value="1" autocomplete="off">
                                            <span class="ss-status-toggle__control">
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                            </span>
                                            <span class="ss-status-toggle__copy">
                                                <strong>Delete</strong>
                                                <small>Remove documents</small>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="acc__input-error error-checkboxs"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="updateRole" class="ss-btn ss-btn--primary">
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
    @vite('resources/js/document-role-and-permission.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
