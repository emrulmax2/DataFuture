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
        $activityCategories = [
            'General',
            'Assignment Brief',
            'Unit Handbook',
            'Harvard Referencing',
            'Lecture/Topic',
        ];
        $placeholderImage = asset('build/assets/images/placeholders/200x200.jpg');
    @endphp

    <div id="siteSettingsPage" class="ss-page ss-elearning-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Site Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>E-Learning Activity Settings</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="monitor"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Manage the e-learning activity labels, icons, reminders and availability used in course planning.</p>
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
                    @php($settingsSidebarIcon = 'frame')
                    @php($settingsSidebarSubtitle = 'E-learning setup')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-card ss-elearning-card">
                        <div class="ss-table-card__header">
                            <h2>E-Learning Activity List</h2>
                            <button data-tw-toggle="modal" data-tw-target="#addELearningActivityModal" type="button" class="add_btn ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add New Activity
                            </button>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query">
                                        <i data-lucide="search"></i>
                                        <input id="query" name="query" type="text" placeholder="Search category...">
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Status</span>
                                    <label class="ss-filter-select" for="status">
                                        <select id="status" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
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
                            <div id="ELearningActivityList" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="addELearningActivityModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-elearning-modal-dialog">
                <form method="POST" action="#" id="addELearningActivityForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-elearning-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add E-Learning Activity</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>

                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-elearning-form-grid">
                                <div class="ss-modal-field ss-elearning-logo-field">
                                    <label>Activity Logo <span>*</span></label>
                                    <label for="userPhotoAdd" class="ss-elearning-logo-picker">
                                        <img alt="Activity logo preview" class="userImageAdd" id="userImageAdd" data-placeholder="{{ $placeholderImage }}" src="{{ $placeholderImage }}">
                                        <span><i data-lucide="camera"></i></span>
                                    </label>
                                    <small data-ss-upload-name>No file selected</small>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="logo" class="logo ss-elearning-logo-input" id="userPhotoAdd">
                                    <div class="acc__input-error error-logo"></div>
                                </div>

                                <div class="ss-elearning-fields">
                                    <div class="ss-modal-grid">
                                        <div class="ss-modal-field">
                                            <label for="add_activity_name">Activity Label <span>*</span></label>
                                            <input id="add_activity_name" type="text" name="name" class="ss-modal-input form-control name" placeholder="Activity label">
                                            <div class="acc__input-error error-name"></div>
                                        </div>

                                        <div class="ss-modal-field">
                                            <label for="short_code">Short Code <span>*</span></label>
                                            <input id="short_code" type="text" name="short_code" class="ss-modal-input form-control short_code" placeholder="Short code">
                                            <div class="acc__input-error error-short_code"></div>
                                        </div>

                                        <div class="ss-modal-field">
                                            <label for="category">Category <span>*</span></label>
                                            <label class="ss-modal-select" for="category">
                                                <select id="category" name="category" class="form-control category">
                                                    <option value="">Please Select</option>
                                                    @foreach($activityCategories as $category)
                                                        <option value="{{ $category }}">{{ $category }}</option>
                                                    @endforeach
                                                </select>
                                                <i data-lucide="chevron-down"></i>
                                            </label>
                                            <div class="acc__input-error error-category"></div>
                                        </div>

                                        <div class="ss-modal-field">
                                            <label for="days_reminder">Days for Reminders</label>
                                            <input id="days_reminder" type="number" min="0" name="days_reminder" class="ss-modal-input form-control days_reminder" placeholder="0">
                                            <div class="acc__input-error error-days_reminder"></div>
                                        </div>

                                        <div class="ss-modal-field ss-modal-field--full ss-document-choices">
                                            <div class="ss-document-choices__heading">
                                                <span>Activity Options</span>
                                                <small>Choose how this activity appears in planning.</small>
                                            </div>
                                            <div class="ss-document-toggle-grid ss-elearning-toggle-grid">
                                                <label class="ss-status-toggle" for="has_week">
                                                    <input id="has_week" name="has_week" value="1" type="checkbox" autocomplete="off">
                                                    <span class="ss-status-toggle__control">
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                    </span>
                                                    <span class="ss-status-toggle__copy">
                                                        <strong>Repeat Weekly</strong>
                                                        <small>Appears across weeks</small>
                                                    </span>
                                                </label>

                                                <label class="ss-status-toggle" for="is_mandatory">
                                                    <input id="is_mandatory" name="is_mandatory" value="1" type="checkbox" autocomplete="off">
                                                    <span class="ss-status-toggle__control">
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                    </span>
                                                    <span class="ss-status-toggle__copy">
                                                        <strong>Mandatory</strong>
                                                        <small>Required activity</small>
                                                    </span>
                                                </label>

                                                <label class="ss-status-toggle ss-elearning-active-toggle" for="active">
                                                    <input id="active" name="active" checked value="1" type="checkbox" autocomplete="off">
                                                    <span class="ss-status-toggle__control">
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                    </span>
                                                    <span class="ss-status-toggle__copy">
                                                        <strong>Active</strong>
                                                        <small>Available for planning</small>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveSettings" class="ss-btn ss-btn--primary">
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

        <div id="editELearningActivityModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-elearning-modal-dialog">
                <form method="POST" action="#" id="editELearningActivityForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-elearning-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit E-Learning Activity</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>

                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-elearning-form-grid">
                                <div class="ss-modal-field ss-elearning-logo-field">
                                    <label>Activity Logo</label>
                                    <label for="userPhotoEdit" class="ss-elearning-logo-picker">
                                        <img alt="Activity logo preview" class="userImageEdit" id="userImageEdit" data-placeholder="{{ $placeholderImage }}" src="{{ $placeholderImage }}">
                                        <span><i data-lucide="camera"></i></span>
                                    </label>
                                    <small data-ss-upload-name>No file selected</small>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="logo" class="logo ss-elearning-logo-input" id="userPhotoEdit">
                                    <div class="acc__input-error error-logo"></div>
                                </div>

                                <div class="ss-elearning-fields">
                                    <div class="ss-modal-grid">
                                        <div class="ss-modal-field">
                                            <label for="edit_activity_name">Activity Label <span>*</span></label>
                                            <input id="edit_activity_name" type="text" name="name" class="ss-modal-input form-control name" placeholder="Activity label">
                                            <div class="acc__input-error error-name"></div>
                                        </div>

                                        <div class="ss-modal-field">
                                            <label for="edit_short_code">Short Code <span>*</span></label>
                                            <input id="edit_short_code" type="text" name="short_code" class="ss-modal-input form-control short_code" placeholder="Short code">
                                            <div class="acc__input-error error-short_code"></div>
                                        </div>

                                        <div class="ss-modal-field">
                                            <label for="edit_category">Category <span>*</span></label>
                                            <label class="ss-modal-select" for="edit_category">
                                                <select id="edit_category" name="category" class="form-control category">
                                                    <option value="">Please Select</option>
                                                    @foreach($activityCategories as $category)
                                                        <option value="{{ $category }}">{{ $category }}</option>
                                                    @endforeach
                                                </select>
                                                <i data-lucide="chevron-down"></i>
                                            </label>
                                            <div class="acc__input-error error-category"></div>
                                        </div>

                                        <div class="ss-modal-field">
                                            <label for="edit_days_reminder">Days for Reminders</label>
                                            <input id="edit_days_reminder" type="number" min="0" name="days_reminder" class="ss-modal-input form-control days_reminder" placeholder="0">
                                            <div class="acc__input-error error-days_reminder"></div>
                                        </div>

                                        <div class="ss-modal-field ss-modal-field--full ss-document-choices">
                                            <div class="ss-document-choices__heading">
                                                <span>Activity Options</span>
                                                <small>Choose how this activity appears in planning.</small>
                                            </div>
                                            <div class="ss-document-toggle-grid ss-elearning-toggle-grid">
                                                <label class="ss-status-toggle" for="edit_has_week">
                                                    <input id="edit_has_week" name="has_week" value="1" type="checkbox" autocomplete="off">
                                                    <span class="ss-status-toggle__control">
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                    </span>
                                                    <span class="ss-status-toggle__copy">
                                                        <strong>Repeat Weekly</strong>
                                                        <small>Appears across weeks</small>
                                                    </span>
                                                </label>

                                                <label class="ss-status-toggle" for="edit_is_mandatory">
                                                    <input id="edit_is_mandatory" name="is_mandatory" value="1" type="checkbox" autocomplete="off">
                                                    <span class="ss-status-toggle__control">
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                    </span>
                                                    <span class="ss-status-toggle__copy">
                                                        <strong>Mandatory</strong>
                                                        <small>Required activity</small>
                                                    </span>
                                                </label>

                                                <label class="ss-status-toggle ss-elearning-active-toggle" for="edit_active">
                                                    <input id="edit_active" name="active" value="1" type="checkbox" autocomplete="off">
                                                    <span class="ss-status-toggle__control">
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                    </span>
                                                    <span class="ss-status-toggle__copy">
                                                        <strong>Active</strong>
                                                        <small>Available for planning</small>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        <input type="hidden" name="id" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" disabled id="updateSettings" class="ss-btn ss-btn--primary">
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
    @vite('resources/js/e-learning-activity-settings.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
