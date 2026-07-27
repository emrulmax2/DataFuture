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
    <div id="siteSettingsPage" class="ss-page ss-internal-link-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Site Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Internal Site Links</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="link-2"></i>
                    </span>
                    <div>
                        <h1>Internal Site Links</h1>
                        <p>Manage dashboard shortcuts, staff/student visibility and parent link groups.</p>
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
                    @php
                        $settingsSidebarIcon = 'link-2';
                        $settingsSidebarSubtitle = 'Dashboard shortcuts';
                    @endphp
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-card ss-internal-link-card">
                        <div class="ss-table-card__header">
                            <div>
                                <h2>Internal Site List</h2>
                                <p>Parent links expand to show their child shortcuts</p>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#uploadEmployeeDocumentModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add Site Link
                            </button>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query">
                                        <i data-lucide="search"></i>
                                        <input id="query" name="query" type="text" placeholder="Search links...">
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
                            <div id="internalLinkTableId" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="uploadEmployeeDocumentModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide ss-internal-link-modal-dialog">
                <div class="modal-content ss-settings-modal ss-internal-link-modal">
                    <div class="ss-settings-modal__header">
                        <div>
                            <span></span>
                            <h2>Add Site Link</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <div class="modal-body ss-settings-modal__body">
                        <form method="post" action="{{ route('internal-link.store') }}" class="dropzone ss-dropzone ss-internal-link-dropzone" id="uploadDocumentForm" enctype="multipart/form-data">
                            @csrf
                            <div class="fallback">
                                <input name="file" type="file">
                            </div>
                            <div class="dz-message" data-dz-message>
                                <i data-lucide="image-plus"></i>
                                <strong>Drop image here or click to upload.</strong>
                                <small>PNG, JPG, GIF or SVG. Max file size 20MB.</small>
                            </div>
                            <input type="hidden" name="name" value="">
                            <input type="hidden" name="parent_id" value="">
                            <input type="hidden" name="link" value="">
                            <input type="hidden" name="available_staff" value="">
                            <input type="hidden" name="available_student" value="">
                            <input type="hidden" name="description" value="">
                            <input type="hidden" name="start_date" value="">
                            <input type="hidden" name="end_date" value="">
                            <input type="hidden" name="active" value="">
                        </form>

                        <div class="ss-modal-grid ss-internal-link-fields">
                            <div class="ss-modal-field">
                                <label for="add_internal_link_name">Name <span>*</span></label>
                                <input id="add_internal_link_name" type="text" name="name_status" class="ss-modal-input name_status" placeholder="Site link name">
                                <div class="acc__input-error error-name_status"></div>
                            </div>
                            <div class="ss-modal-field">
                                <label for="add_internal_link_url">Link</label>
                                <input id="add_internal_link_url" type="url" name="link_status" class="ss-modal-input link_status" placeholder="https://example.com">
                            </div>
                            <div class="ss-modal-field ss-modal-field--full">
                                <label for="add_internal_link_parent">Parent Category</label>
                                <label class="ss-modal-select" for="add_internal_link_parent">
                                    <select id="add_internal_link_parent" name="parent_category">
                                        <option value="">Please Select</option>
                                        @if(!empty($parents))
                                            @foreach($parents as $crs)
                                                <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <i data-lucide="chevron-down"></i>
                                </label>
                            </div>
                            <div class="ss-modal-field">
                                <label for="add_internal_link_start">Started On</label>
                                <input id="add_internal_link_start" type="text" name="start_date_status" class="datepicker date-picker ss-modal-input start_date_status" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-start_date_status"></div>
                            </div>
                            <div class="ss-modal-field">
                                <label for="add_internal_link_end">Ended On</label>
                                <input id="add_internal_link_end" type="text" name="end_date_status" class="datepicker date-picker ss-modal-input end_date_status" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-end_date_status"></div>
                            </div>
                            <div class="ss-modal-field ss-modal-field--full">
                                <label for="add_internal_link_description">Description</label>
                                <textarea id="add_internal_link_description" name="description_status" class="ss-modal-input ss-modal-textarea description_status" rows="4" placeholder="Short description for this link"></textarea>
                            </div>
                            <div class="ss-modal-field ss-modal-field--full">
                                <label>Visibility</label>
                                <div class="ss-internal-toggle-grid">
                                    <label class="ss-status-toggle ss-internal-link-toggle" for="add_available_staff_status">
                                        <input id="add_available_staff_status" name="available_staff_status" value="1" type="checkbox" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Staff</strong>
                                            <small>Visible to staff users</small>
                                        </span>
                                    </label>
                                    <label class="ss-status-toggle ss-internal-link-toggle" for="add_available_student_status">
                                        <input id="add_available_student_status" name="available_student_status" value="1" type="checkbox" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Student</strong>
                                            <small>Visible to student users</small>
                                        </span>
                                    </label>
                                    <label class="ss-status-toggle ss-internal-link-toggle" for="add_active_status">
                                        <input id="add_active_status" name="active_status" value="1" type="checkbox" checked autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Active</strong>
                                            <small>Available on dashboards</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer ss-settings-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                        <button type="button" id="uploadEmpDocBtn" class="ss-btn ss-btn--primary">
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

        <div id="uploadEmployeeDocumentModalEdit" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide ss-internal-link-modal-dialog">
                <div class="modal-content ss-settings-modal ss-internal-link-modal">
                    <div class="ss-settings-modal__header">
                        <div>
                            <span></span>
                            <h2>Edit Site Link</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <div class="modal-body ss-settings-modal__body">
                        <div class="ss-internal-current-image" data-current-image hidden>
                            <span data-current-image-preview></span>
                            <div>
                                <strong>Current Image</strong>
                                <small data-current-image-name>No image uploaded</small>
                            </div>
                        </div>

                        <form method="post" action="{{ route('internal-link.update') }}" class="dropzone ss-dropzone ss-internal-link-dropzone" id="uploadDocumentFormEdit" enctype="multipart/form-data">
                            @csrf
                            <div class="fallback">
                                <input name="file" type="file">
                            </div>
                            <div class="dz-message" data-dz-message>
                                <i data-lucide="image-plus"></i>
                                <strong>Drop new image here or click to replace.</strong>
                                <small>Leave empty to keep the current image.</small>
                            </div>
                            <input type="hidden" name="id" value="">
                            <input type="hidden" name="name" value="">
                            <input type="hidden" name="parent_id" value="">
                            <input type="hidden" name="link" value="">
                            <input type="hidden" name="available_staff" value="">
                            <input type="hidden" name="available_student" value="">
                            <input type="hidden" name="description" value="">
                            <input type="hidden" name="start_date" value="">
                            <input type="hidden" name="end_date" value="">
                            <input type="hidden" name="active" value="">
                        </form>

                        <div class="ss-modal-grid ss-internal-link-fields">
                            <div class="ss-modal-field">
                                <label for="edit_internal_link_name">Name <span>*</span></label>
                                <input id="edit_internal_link_name" type="text" name="name_status" class="ss-modal-input name_status" placeholder="Site link name">
                                <div class="acc__input-error error-name_status"></div>
                            </div>
                            <div class="ss-modal-field">
                                <label for="edit_internal_link_url">Link</label>
                                <input id="edit_internal_link_url" type="url" name="link_status" class="ss-modal-input link_status" placeholder="https://example.com">
                            </div>
                            <div class="ss-modal-field ss-modal-field--full">
                                <label for="edit_internal_link_parent">Parent Category</label>
                                <label class="ss-modal-select" for="edit_internal_link_parent">
                                    <select id="edit_internal_link_parent" name="parent_category">
                                        <option value="">Please Select</option>
                                        @if(!empty($parents))
                                            @foreach($parents as $crs)
                                                <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <i data-lucide="chevron-down"></i>
                                </label>
                            </div>
                            <div class="ss-modal-field">
                                <label for="edit_internal_link_start">Started On</label>
                                <input id="edit_internal_link_start" type="text" name="start_date_status" class="datepicker date-picker ss-modal-input start_date_status" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-start_date_status"></div>
                            </div>
                            <div class="ss-modal-field">
                                <label for="edit_internal_link_end">Ended On</label>
                                <input id="edit_internal_link_end" type="text" name="end_date_status" class="datepicker date-picker ss-modal-input end_date_status" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-end_date_status"></div>
                            </div>
                            <div class="ss-modal-field ss-modal-field--full">
                                <label for="edit_internal_link_description">Description</label>
                                <textarea id="edit_internal_link_description" name="description_status" class="ss-modal-input ss-modal-textarea description_status" rows="4" placeholder="Short description for this link"></textarea>
                            </div>
                            <div class="ss-modal-field ss-modal-field--full">
                                <label>Visibility</label>
                                <div class="ss-internal-toggle-grid">
                                    <label class="ss-status-toggle ss-internal-link-toggle" for="edit_available_staff_status">
                                        <input id="edit_available_staff_status" name="available_staff_status" value="1" type="checkbox" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Staff</strong>
                                            <small>Visible to staff users</small>
                                        </span>
                                    </label>
                                    <label class="ss-status-toggle ss-internal-link-toggle" for="edit_available_student_status">
                                        <input id="edit_available_student_status" name="available_student_status" value="1" type="checkbox" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Student</strong>
                                            <small>Visible to student users</small>
                                        </span>
                                    </label>
                                    <label class="ss-status-toggle ss-internal-link-toggle" for="edit_active_status">
                                        <input id="edit_active_status" name="active_status" value="1" type="checkbox" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Active</strong>
                                            <small>Available on dashboards</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer ss-settings-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                        <button type="button" id="uploadEmpDocBtnEdit" class="ss-btn ss-btn--primary">
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

        <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content ss-success-modal ss-success-modal--warning">
                    <div class="modal-body p-0">
                        <div class="ss-success-modal__body">
                            <i data-lucide="alert-octagon" class="ss-success-modal__icon"></i>
                            <div class="warningModalTitle"></div>
                            <p class="warningModalDesc"></p>
                        </div>
                        <div class="ss-success-modal__footer">
                            <button type="button" data-action="DISMISS" class="warningCloser ss-btn ss-btn--primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/internallink.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
